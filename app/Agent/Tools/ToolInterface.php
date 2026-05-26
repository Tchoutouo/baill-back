<?php

namespace App\Agent\Tools;

interface ToolInterface
{
    public function name(): string;
    public function description(): string;
    public function inputSchema(): array;
    public function execute(array $input, array $context = []): array;
}
