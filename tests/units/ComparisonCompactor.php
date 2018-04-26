<?php
namespace Jlttt\cleanCode15\tests\units;

use atoum;

class ComparisonCompactor extends atoum
{
    public function testMessage()
    {
        $failure = $this->newTestedInstance(0, "b", "c")->compact("a");
        $this->string($failure)->isEqualTo("a expected:<[b]> but was:<[c]>");
    }

    public function testStartSame()
    {
        $failure = $this->newTestedInstance(1, "ba", "bc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<b[a]> but was:<b[c]>");
    }

    public function testEndSame()
    {
        $failure = $this->newTestedInstance(1, "ab", "cb")->compact(null);
        $this->string($failure)->isEqualTo("expected:<[a]b> but was:<[c]b>");
    }

    public function testSame()
    {
        $failure = $this->newTestedInstance(1, "ab", "ab")->compact(null);
        $this->string($failure)->isEqualTo("expected:<ab> but was:<ab>");
    }

    public function testNoContextStartAndEndSame()
    {
        $failure = $this->newTestedInstance(0, "abc", "adc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<...[b]...> but was:<...[d]...>");
    }

    public function testStartAndEndContext()
    {
        $failure = $this->newTestedInstance(1, "abc", "adc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<a[b]c> but was:<a[d]c>");
    }

    public function testStartAndEndContextWithEllipses()
    {
        $failure = $this->newTestedInstance(1, "abcde", "abfde")->compact(null);
        $this->string($failure)->isEqualTo("expected:<...b[c]d...> but was:<...b[f]d...>");
    }

    public function testComparisonErrorStartSameComplete()
    {
        $failure = $this->newTestedInstance(2, "ab", "abc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<ab[]> but was:<ab[c]>");
    }

    public function testComparisonErrorEndSameComplete()
    {
        $failure = $this->newTestedInstance(0, "bc", "abc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<[]...> but was:<[a]...>");
    }

    public function testComparisonErrorEndSameCompleteContext()
    {
        $failure = $this->newTestedInstance(2, "bc", "abc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<[]bc> but was:<[a]bc>");
    }

    public function testComparisonErrorOverlapingMatches()
    {
        $failure = $this->newTestedInstance(0, "abc", "abbc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<...[]...> but was:<...[b]...>");
    }

    public function testComparisonErrorOverlapingMatchesContext()
    {
        $failure = $this->newTestedInstance(2, "abc", "abbc")->compact(null);
        $this->string($failure)->isEqualTo("expected:<ab[]c> but was:<ab[b]c>");
    }

    public function testComparisonErrorOverlapingMatches2()
    {
        $failure = $this->newTestedInstance(0, "abcdde", "abcde")->compact(null);
        $this->string($failure)->isEqualTo("expected:<...[d]...> but was:<...[]...>");
    }

    public function testComparisonErrorOverlapingMatches2Context()
    {
        $failure = $this->newTestedInstance(2, "abcdde", "abcde")->compact(null);
        $this->string($failure)->isEqualTo("expected:<...cd[d]e> but was:<...cd[]e>");
    }

    public function testComparisonErrorWithActualNull()
    {
        $failure = $this->newTestedInstance(0, "a", null)->compact(null);
        $this->string($failure)->isEqualTo("expected:<a> but was:<null>");
    }

    public function testComparisonErrorWithActualNullContext()
    {
        $failure = $this->newTestedInstance(2, "a", null)->compact(null);
        $this->string($failure)->isEqualTo("expected:<a> but was:<null>");
    }

    public function testComparisonErrorWithExpectedNull()
    {
        $failure = $this->newTestedInstance(0, null, "a")->compact(null);
        $this->string($failure)->isEqualTo("expected:<null> but was:<a>");
    }

    public function testComparisonErrorWithExpectedNullContext()
    {
        $failure = $this->newTestedInstance(2, null, "a")->compact(null);
        $this->string($failure)->isEqualTo("expected:<null> but was:<a>");
    }

    public function testBug609972()
    {
        $failure = $this->newTestedInstance(10, "S&P500", "0")->compact(null);
        $this->string($failure)->isEqualTo("expected:<[S&P50]0> but was:<[]0>");
    }
}
