<?php

namespace app\modules\module_page_reviews\ext\Http;

final class PageHandleResult
{
    private $iframeCode;
    private $iframeMessage;
    private $context;

    private function __construct(?int $iframeCode, ?string $iframeMessage, array $context)
    {
        $this->iframeCode = $iframeCode;
        $this->iframeMessage = $iframeMessage;
        $this->context = $context;
    }

    public static function ok(array $context): self
    {
        return new self(null, null, $context);
    }

    public static function iframe(int $code, string $message): self
    {
        return new self($code, $message, []);
    }

    public function isOk(): bool
    {
        return $this->iframeCode === null;
    }

    public function renderIframe(): void
    {
        if ($this->iframeCode === null) {
            return;
        }

        get_iframe($this->iframeCode, $this->iframeMessage ?? '');
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
