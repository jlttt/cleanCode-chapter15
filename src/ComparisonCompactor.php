<?php

namespace Jlttt\cleanCode15;

class ComparisonCompactor
{

    const ELLIPSIS = "...";
    const DELTA_END = "]";
    const DELTA_START = "[";

    private $fContextLength;
    private $fExpected;
    private $fActual;
    private $fPrefix;
    private $fSuffix;

    public function __construct(int $contextLength, ?string $expected, ?string $actual)
    {
        $this->fContextLength = $contextLength;
        $this->fExpected = $expected;
        $this->fActual = $actual;
    }

    public function compact(?string $message): string
    {
        if (is_null($this->fExpected) || is_null($this->fActual) || $this->areStringsEqual()) {
            return Asserter::format($message, $this->fExpected, $this->fActual);
        }
        $this->findCommonPrefix();
        $this->findCommonSuffix();
        $expected = $this->compactString($this->fExpected);
        $actual = $this->compactString($this->fActual);
        return Asserter::format($message, $expected, $actual);
    }

    private function compactString(string $source): string
    {
        $result = self::DELTA_START .
            substr($source, $this->fPrefix, strlen($source) - $this->fSuffix + 1 - $this->fPrefix) .
            self::DELTA_END;

        if ($this->fPrefix > 0) {
            $result = $this->computeCommonPrefix() . $result;
        }

        if ($this->fSuffix > 0) {
            $result = $result . $this->computeCommonSuffix();
        }

        return $result;
    }

    private function findCommonPrefix()
    {
        $this->fPrefix = 0;
        $end = min(strlen($this->fExpected), strlen($this->fActual));
        for (; $this->fPrefix < $end; $this->fPrefix++) {
            if ($this->fExpected[$this->fPrefix] != $this->fActual[$this->fPrefix]) {
                break;
            }
        }
    }

    private function findCommonSuffix()
    {
        $expectedSuffix = strlen($this->fExpected) - 1;
        $actualSuffix = strlen($this->fActual) - 1;
        for (; $actualSuffix >= $this->fPrefix && $expectedSuffix >= $this->fPrefix; $actualSuffix--, $expectedSuffix--) {
            if ($this->fExpected[$expectedSuffix] != $this->fActual[$actualSuffix]) {
                break;
            }
        }
        $this->fSuffix = strlen($this->fExpected) - $expectedSuffix;
    }

    private function computeCommonPrefix()
    {
        return ($this->fPrefix > $this->fContextLength ? self::ELLIPSIS : "") .
        substr($this->fExpected, max(0, $this->fPrefix - $this->fContextLength), $this->fPrefix - max(0, $this->fPrefix - $this->fContextLength));
    }

    private function computeCommonSuffix()
    {
        $end = min(strlen($this->fExpected) - $this->fSuffix + 1 + $this->fContextLength, strlen($this->fExpected));
        return substr($this->fExpected, strlen($this->fExpected) - $this->fSuffix + 1, $end - (strlen($this->fExpected) - $this->fSuffix + 1)) .
            ((strlen($this->fExpected) - $this->fSuffix + 1 < strlen($this->fExpected) - $this->fContextLength) ? self::ELLIPSIS : "");
    }

    private function areStringsEqual(): bool
    {
        return $this->fExpected === $this->fActual;
    }
}
