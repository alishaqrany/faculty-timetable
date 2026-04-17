<?php

class View
{
    private string $viewPath;
    private array $data = [];
    private ?string $layoutFile = null;
    private array $sections = [];
    private ?string $currentSection = null;

    public function __construct()
    {
        $this->viewPath = APP_ROOT . '/app/Views';
    }

    /**
     * Render a view file with optional data.
     */
    public function render(string $view, array $data = []): string
    {
        $this->data = $data;
        $file = $this->viewPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($file)) {
            throw new RuntimeException("View not found: $view ($file)");
        }

        $content = $this->capture($file, $data);

        // If a layout was set, wrap content
        if ($this->layoutFile) {
            $this->sections['content'] = $content;
            $layoutPath = $this->viewPath . '/' . str_replace('.', '/', $this->layoutFile) . '.php';
            $this->layoutFile = null; // Reset
            $content = $this->capture($layoutPath, $data);
        }

        return $content;
    }

    /**
     * Set the layout for this view.
     */
    public function layout(string $layout): void
    {
        $this->layoutFile = $layout;
    }

    /**
     * Begin a named section.
     */
    public function section(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }

    /**
     * End the current section.
     */
    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    /**
     * Yield a section's content (used in layouts).
     */
    public function yield(string $name, string $default = ''): void
    {
        echo $this->sections[$name] ?? $default;
    }

    /**
     * Include a partial view.
     */
    public function include(string $partial, array $data = []): void
    {
        $file = $this->viewPath . '/' . str_replace('.', '/', $partial) . '.php';
        if (file_exists($file)) {
            extract(array_merge($this->data, $data));
            include $file;
        }
    }

    /**
     * Escape output for HTML.
     */
    public function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Capture a file's output.
     */
    private function capture(string $file, array &$data): string
    {
        extract($data);
        $__view = $this;
        ob_start();
        include $file;
        // Propagate __page_title and __breadcrumb set inside views to layout
        if (isset($__page_title)) $data['__page_title'] = $__page_title;
        if (isset($__breadcrumb)) $data['__breadcrumb'] = $__breadcrumb;
        return ob_get_clean();
    }
}
