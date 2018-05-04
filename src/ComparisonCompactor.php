<?php

namespace Jlttt\cleanCode15;

class ComparisonCompactor
{

    const ELLIPSIS = "...";
    const DELTA_END = "]";
    const DELTA_START = "[";

    private $contextLength;
    private $expected;
    private $actual;
    private $minimalLength;
    private $prefixLength;
    private $suffixLength;

    public function __construct(int $contextLength, ?string $expected, ?string $actual)
    {
        $this->contextLength = $contextLength;
        $this->expected = $expected;
        $this->actual = $actual;
        $this->minimalLength = $this->computeMinimalLength();
    }

    private function computeMinimalLength()
    {
        return min(array_map('strlen', $this->filterNull([$this->expected, $this->actual])));
    }

    private function filterNull($input)
    {
        return array_filter(
            $input,
            function ($item) {
                return !is_null($item);
            }
        );
    }

    public function getResult(?string $message): string
    {
        $compactedExpected = $this->expected;
        $compactedActual = $this->actual;
        if ($this->isCompactable()) {
            $this->prefixLength = $this->computeCommonPrefixLength();
            $this->suffixLength = $this->computeCommonSuffixLength();
            $compactedExpected = $this->compact($this->expected);
            $compactedActual = $this->compact($this->actual);
        }
        return Asserter::format($message, $compactedExpected, $compactedActual);
    }

    private function isCompactable(): bool
    {
        return !is_null($this->expected) && !is_null($this->actual) && !$this->areStringsEqual();
    }

    private function computeCommonPrefixLength(string $first = null, string $second = null, int $maxIndex = null)
    {
        $default = [
            'first' => $this->expected,
            'second' => $this->actual,
            'maxIndex' => $this->minimalLength
        ];
        $arguments = $this->filterNull(compact('first', 'second', 'maxIndex'));
        $arguments = array_merge($default, $arguments);
        extract($arguments);

        for ($prefixLength = 0; $prefixLength < $maxIndex; $prefixLength++) {
            if ($first[$prefixLength] != $second[$prefixLength]) {
                return $prefixLength;
            }
        }
        return $maxIndex;
    }

    private function computeCommonSuffixLength()
    {
        return $this->computeCommonPrefixLength(
            strrev($this->expected),
            strrev($this->actual),
            $this->minimalLength - $this->prefixLength
        );
    }

    private function compact(string $source): string
    {
        return $this->getCommonPrefix() .
            self::DELTA_START .
            $this->getDelta($source) .
            self::DELTA_END .
            $this->getCommonSuffix();
    }

    private function getDelta(string $source)
    {
        $deltaLength = strlen($source) - ($this->suffixLength + $this->prefixLength);
        return substr($source, $this->prefixLength, $deltaLength);
    }

    private function getCommonPrefix()
    {
        return $this->computePrefix(
            $this->expected,
            $this->prefixLength,
            self::ELLIPSIS
        );
    }

    private function getCommonSuffix()
    {
        $reverseExpected = strrev($this->expected);
        $reverseEllipsis = strrev(self::ELLIPSIS);
        $reverseSuffix = $this->computePrefix(
            $reverseExpected,
            $this->suffixLength,
            $reverseEllipsis
        );
        return strrev($reverseSuffix);
    }

    private function computePrefix($source, $prefixLength, $ellipsis)
    {
        $prefix = substr(
            $source,
            max(0, $prefixLength - $this->contextLength),
            min($prefixLength, $this->contextLength)
        );
        if ($prefixLength > $this->contextLength) {
            $prefix = $ellipsis . $prefix;
        }
        return $prefix;
    }

    private function areStringsEqual(): bool
    {
        return $this->expected === $this->actual;
    }
}
