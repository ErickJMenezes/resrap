<?php

declare(strict_types = 1);

namespace Resrap\Component\Scanner;

final class InputBuffer
{
    private int $offset = 0;

    private int $line = 1;

    private int $column = 1;

    public Position $position {
        get => new Position(
            $this->offset,
            $this->line,
            $this->column,
        );
    }

    public bool $eof {
        get => strlen($this->content) === 0;
    }

    public function __construct(private(set) string $content) {}

    public function consume(int $length): string
    {
        $consumed = substr($this->content, 0, $length);
        $this->content = substr($this->content, $length);

        // Atualiza posição
        $this->offset += $length;
        $lines = substr_count($consumed, "\n");
        if ($lines > 0) {
            $this->line += $lines;
            $lastNewline = strrpos($consumed, "\n");
            $this->column = strlen($consumed) - $lastNewline;
        } else {
            $this->column += $length;
        }

        return $consumed;
    }
}
