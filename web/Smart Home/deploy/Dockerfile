FROM ruby:3.2-slim

ENV APP_HOME=/app \
    BUNDLE_WITHOUT="development:test" \
    RACK_ENV=production \
    LANG=C.UTF-8

WORKDIR $APP_HOME

RUN apt-get update \
 && apt-get install -y --no-install-recommends ca-certificates build-essential curl \
 && rm -rf /var/lib/apt/lists/*

COPY files/ ./

# Ensure scripts are executable even if the host lost +x bits
RUN chmod +x /app/entrypoint.sh

RUN gem install bundler -v "~> 2.5" --no-document \
 && bundle install

RUN useradd -m -u 10001 appuser \
 && touch /flag.txt \
 && chown appuser:appuser /flag.txt \
 && chown -R appuser:appuser $APP_HOME

EXPOSE 8080

USER appuser

ENTRYPOINT ["/app/entrypoint.sh"]
CMD ["ruby", "app.rb", "-o", "0.0.0.0", "-p", "8080"]
