require 'sinatra'
require 'json'
require 'time'
require 'fileutils'
require 'securerandom'
require 'uri'
 

set :bind, '0.0.0.0'
set :port, (ENV.fetch('PORT', '8080').to_i)
set :environment, :production
set :server, :webrick
set :public_folder, File.join(__dir__, 'public')
set :views, File.join(__dir__, 'views')

BOOT_TIME = Time.now

SESSION_COOKIE = 'shcc_sid'.freeze

ACTIVITY_DIR = File.join(__dir__, 'data', 'activity').freeze
ACTIVITY_MAX_LINES = 20
STALE_LOG_SECONDS = 20 * 60
HOUSEKEEPING_INTERVAL_SECONDS = 60

HOUSEKEEPING_MUTEX = Mutex.new
$last_housekeeping_at = Time.at(0)

DEVICES = [
  { key: 'lights',      room: 'Living Room', name: 'Living room lights' },
  { key: 'garage',      room: 'Garage',      name: 'Garage door' },
  { key: 'lock',        room: 'Entry',       name: 'Front door lock' },
  { key: 'camera',      room: 'Porch',       name: 'Security camera' },
  { key: 'alarm',       room: 'Entry',       name: 'Alarm system' },
  { key: 'thermostat',  room: 'Hallway',     name: 'Thermostat' }
].freeze

HOME_ROOMS = [
  { name: 'Living Room', meta: 'Lights' },
  { name: 'Entry',       meta: 'Lock • Alarm' },
  { name: 'Garage',      meta: 'Door' },
  { name: 'Porch',       meta: 'Camera' },
  { name: 'Hallway',     meta: 'Thermostat' }
].freeze

DEVICE_COUNT = DEVICES.length
ROOM_COUNT = HOME_ROOMS.length

before do
  headers 'X-Content-Type-Options' => 'nosniff',
          'X-Frame-Options' => 'DENY',
          'Referrer-Policy' => 'no-referrer'
end

helpers do
  def current_sid
    sid = request.cookies[SESSION_COOKIE]
    valid = sid.is_a?(String) && sid.match?(/\A[a-f0-9]{32}\z/)
    valid ? sid : nil
  end

  def run_housekeeping!
    now = Time.now    
    return if (now - $last_housekeeping_at) < HOUSEKEEPING_INTERVAL_SECONDS

    HOUSEKEEPING_MUTEX.synchronize do
      now = Time.now
      return if (now - $last_housekeeping_at) < HOUSEKEEPING_INTERVAL_SECONDS
      $last_housekeeping_at = now

      begin
        FileUtils.mkdir_p(ACTIVITY_DIR)
        Dir.glob(File.join(ACTIVITY_DIR, '*.log')).each do |path|
          begin
            st = File.stat(path)
            File.delete(path) if (now - st.mtime) > STALE_LOG_SECONDS
          rescue
          end
        end
      rescue
      end
    end
  end

  def session_id
    run_housekeeping!
    sid = request.cookies[SESSION_COOKIE]
    valid = sid.is_a?(String) && sid.match?(/\A[a-f0-9]{32}\z/)
    unless valid
      sid = SecureRandom.hex(16)
      response.set_cookie(SESSION_COOKIE, value: sid, path: '/', httponly: true, same_site: 'Lax')
    end
    sid
  end

  def activity_path_for(sid)
    File.join(ACTIVITY_DIR, "#{sid}.log")
  end

  def activity_path
    activity_path_for(session_id)
  end


  def activity_log(message, kind: 'system')
    FileUtils.mkdir_p(File.dirname(activity_path))
    entry = {
      ts: Time.now.iso8601,
      kind: kind.to_s,
      message: message.to_s
    }

    File.open(activity_path, 'a+') do |f|
      f.flock(File::LOCK_EX)
      f.seek(0, IO::SEEK_END)
      f.puts(entry.to_json)
      f.flush

      f.rewind
      lines = f.read.to_s.split("\n")
      if lines.length > ACTIVITY_MAX_LINES
        lines = lines.last(ACTIVITY_MAX_LINES)
        f.rewind
        f.truncate(0)
        f.write(lines.join("\n") + "\n")
        f.flush
      end
    end
  rescue => e
    warn "activity_log failed: #{e.class}: #{e.message}"
  end

  def convert_to_value(text)
    t = text.to_s.strip
    return t if t.empty?
    return t unless t.start_with?('[', '{')
    eval(t)
  end

  def parse_cmd_string(command)
    parts = command.to_s.strip.split(/\s+/)
    raise ArgumentError, 'Empty command' if parts.empty?

    device = parts.shift
    action = parts.shift || ''
    raw_params = parts

    parsed_params = raw_params.map { |p| convert_to_value(p) }
    { device: device, action: action, params: parsed_params }
  end

  def human_activity_message(parsed)
    d = parsed[:device].to_s.upcase
    a = parsed[:action].to_s.upcase

    case [d, a]
    when ['LIGHTS', 'ON']
      'Living room lights turned on'
    when ['LIGHTS', 'OFF']
      'Living room lights turned off'
    when ['LOCK', 'STATUS']
      'Front door lock status checked'
    when ['THERMOSTAT', 'GET']
      'Thermostat reading refreshed'
    when ['GARAGE', 'OPEN']
      'Garage door opened'
    when ['GARAGE', 'CLOSE']
      'Garage door closed'
    when ['CAMERA', 'SNAP']
      'Security camera snapshot captured'
    when ['ALARM', 'ARM']
      'Alarm armed'
    when ['ALARM', 'DISARM']
      'Alarm disarmed'
    when ['SCENE', 'HOME']
      'Scene set to Home'
    when ['SCENE', 'AWAY']
      'Scene set to Away'
    when ['SCENE', 'NIGHT']
      'Scene set to Night'
    when ['SCENE', 'MOVIE']
      'Scene set to Movie'
    else
      base = [parsed[:device], parsed[:action]].compact.join(' ').strip
      base.empty? ? 'Command applied' : "#{base} applied"
    end
  end

  def human_activity_kind(parsed)
    d = parsed[:device].to_s.upcase
    case d
    when 'LIGHTS' then 'lights'
    when 'LOCK', 'GARAGE' then 'lock'
    when 'THERMOSTAT' then 'climate'
    when 'CAMERA', 'ALARM' then 'security'
    else 'system'
    end
  end

  def authorize!
    required = ENV.fetch('PAIRING_TOKEN', 'smart-home')
    provided = request.env['HTTP_X_PAIRING_TOKEN'] || request.cookies['pairing_token']
    halt 401, { error: 'Unauthorized' }.to_json unless provided == required
  end

  def jsonrpc_error(id, code, message)
    {
      jsonrpc: '2.0',
      id: id,
      error: { code: code, message: message }
    }
  end

  def load_recent_activity(limit: ACTIVITY_MAX_LINES)
    sid = current_sid
    return [] unless sid

    path = activity_path_for(sid)
    return [] unless File.exist?(path)

    raw = File.read(path, encoding: 'UTF-8')
    lines = raw.split("\n").last(limit)

    lines.map do |line|
      begin
        obj = JSON.parse(line)
        {
          ts: obj['ts'],
          kind: obj['kind'] || 'system',
          message: obj['message'] || ''
        }
      rescue
        { ts: nil, kind: 'system', message: line.to_s }
      end
    end
  rescue
    []
  end
end

get '/' do
  session_id
  @device_count = DEVICE_COUNT
  @room_count = ROOM_COUNT
  @home_rooms = HOME_ROOMS
  erb :index
end

get '/dashboard' do
  session_id
  @device_count = DEVICE_COUNT
  token = ENV.fetch('PAIRING_TOKEN', 'smart-home')
  unless request.cookies['pairing_token']
    response.set_cookie('pairing_token', value: token, path: '/', httponly: true, same_site: 'Lax')
  end

  erb :dashboard
end

get '/api/activity' do
  content_type :json
  headers 'Cache-Control' => 'no-store'

  items = load_recent_activity(limit: ACTIVITY_MAX_LINES).reverse
  { items: items }.to_json
end



post '/api/rpc' do
  content_type :json
  unless current_sid
    status 401
    return({ error: 'Unauthorized' }.to_json)
  end

  body = request.body.read
  payload = JSON.parse(body)

  id = payload['id']
  method = payload['method']

  unless payload['jsonrpc'] == '2.0'
    status 400
    return jsonrpc_error(id, -32600, 'Invalid Request').to_json
  end

  unless method.is_a?(String)
    status 400
    return jsonrpc_error(id, -32600, 'Invalid Request').to_json
  end

  unless method == 'cmd'
    status 404
    return jsonrpc_error(id, -32601, 'Method not found').to_json
  end

  params = payload['params'] || {}
  cmd = params.is_a?(Hash) ? params['command'] : nil

  begin
    parsed = parse_cmd_string(cmd)

    authorize!

    msg = human_activity_message(parsed)
    kind = human_activity_kind(parsed)
    activity_log(msg, kind: kind)

    { jsonrpc: '2.0', id: id, result: { ok: true, kind: kind, message: msg } }.to_json
  rescue JSON::ParserError
    status 400
    jsonrpc_error(id, -32700, 'Parse error').to_json
  rescue ArgumentError
    status 400
    jsonrpc_error(id, -32602, 'Invalid params').to_json
  rescue
    activity_log('System event recorded')
    status 500
    jsonrpc_error(id, -32603, 'Internal error').to_json
  end
end

not_found do
  content_type :html
  erb :not_found
end
