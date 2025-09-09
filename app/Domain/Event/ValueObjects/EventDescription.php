<?php

namespace App\Domain\Event\ValueObjects;

use InvalidArgumentException;

class EventDescription
{
    private string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $this->sanitize($value);
    }

    private function validate(string $value): void
    {
        $trimmedValue = trim($value);
        
        if (empty($trimmedValue)) {
            throw new InvalidArgumentException('Event description cannot be empty');
        }

        if (strlen($trimmedValue) > 255) {
            throw new InvalidArgumentException('Event description cannot exceed 255 characters');
        }

        if (strlen($trimmedValue) < 10) {
            throw new InvalidArgumentException('Event description must be at least 10 characters long');
        }

        // Business rule: Description should contain meaningful content
        if (!preg_match('/[a-zA-Z]/', $trimmedValue)) {
            throw new InvalidArgumentException('Event description must contain at least one letter');
        }

        // Business rule: No excessive special characters
        if (preg_match('/[!@#$%^&*()]{5,}/', $trimmedValue)) {
            throw new InvalidArgumentException('Event description contains too many consecutive special characters');
        }
    }

    private function sanitize(string $value): string
    {
        // Trim whitespace
        $sanitized = trim($value);
        
        // Replace multiple spaces with single space
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        // Remove excessive line breaks
        $sanitized = preg_replace('/\n{3,}/', "\n\n", $sanitized);
        
        return $sanitized;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getLength(): int
    {
        return strlen($this->value);
    }

    public function getWordCount(): int
    {
        return str_word_count($this->value);
    }

    public function isShort(): bool
    {
        return $this->getLength() <= 50;
    }

    public function isMedium(): bool
    {
        return $this->getLength() > 50 && $this->getLength() <= 150;
    }

    public function isLong(): bool
    {
        return $this->getLength() > 150;
    }

    public function getExcerpt(int $maxLength = 100): string
    {
        if ($this->getLength() <= $maxLength) {
            return $this->value;
        }

        $excerpt = substr($this->value, 0, $maxLength);
        $lastSpace = strrpos($excerpt, ' ');
        
        if ($lastSpace !== false) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }
        
        return $excerpt . '...';
    }

    public function containsKeyword(string $keyword): bool
    {
        return stripos($this->value, $keyword) !== false;
    }

    public function getKeywords(): array
    {
        // Simple keyword extraction - remove common words
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should'];
        
        $words = str_word_count(strtolower($this->value), 1);
        $keywords = array_diff($words, $commonWords);
        
        return array_values(array_unique($keywords));
    }

    public function hasMinimumWords(int $minWords = 5): bool
    {
        return $this->getWordCount() >= $minWords;
    }

    public function equals(EventDescription $other): bool
    {
        return $this->value === $other->value;
    }

    public function contains(string $substring): bool
    {
        return stripos($this->value, $substring) !== false;
    }

    public function startsWith(string $prefix): bool
    {
        return stripos($this->value, $prefix) === 0;
    }

    public function endsWith(string $suffix): bool
    {
        return substr_compare($this->value, $suffix, -strlen($suffix), strlen($suffix), true) === 0;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
