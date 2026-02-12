<?php

namespace App\Solutions;

use Facade\IgnitionContracts\RunnableSolution;

class ReadFileSolution implements RunnableSolution
{
    private $filePath;
    private $variableName;

    public function __construct($variableName = null, $filePath = null)
    {
        $this->variableName = $variableName;
        $this->filePath = $filePath;
    }

    public function getSolutionTitle(): string
    {
        return "Read file content";
    }

    public function getDocumentationLinks(): array
    {
        return [];
    }

    public function getSolutionActionDescription(): string
    {
        return "Read the specified file";
    }

    public function getRunButtonText(): string
    {
        return 'Read file';
    }

    public function getSolutionDescription(): string
    {
        return '';
    }

    public function getRunParameters(): array
    {
        return [
            'variableName' => $this->variableName,
            'filePath' => $this->filePath,
        ];
    }

    public function isRunnable(array $parameters = [])
    {
        return isset($parameters['filePath']) && file_exists($parameters['filePath']);
    }

    public function run(array $parameters = [])
    {
        $filePath = $parameters['filePath'] ?? '';
        
        if (!file_exists($filePath)) {
            throw new \Exception("File not found: {$filePath}");
        }
        
        $content = file_get_contents($filePath);
        
        // Output the content in a way that's visible in the response
        echo json_encode([
            'success' => true,
            'file' => $filePath,
            'content' => $content,
            'base64' => base64_encode($content),
        ]);
        
        // Exit to prevent further processing
        exit(0);
    }
}
