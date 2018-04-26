<?php
namespace Jlttt\cleanCode15\tests\units;

use atoum;

class Asserter extends atoum
{
    public function testAllNull()
    {
        $this->string(\Jlttt\cleanCode15\Asserter::format(null, null, null))->isEqualTo("expected:<null> but was:<null>");
    }

    public function testNullMessage()
    {
        $this->string(\Jlttt\cleanCode15\Asserter::format(null, 'a', 'b'))->isEqualTo("expected:<a> but was:<b>");
    }

    public function testNullExpected() {
        $this->string(\Jlttt\cleanCode15\Asserter::format('a', null, 'b'))->isEqualTo("a expected:<null> but was:<b>");
    }

    public function testNullActual() {
        $this->string(\Jlttt\cleanCode15\Asserter::format('a', 'b', null))->isEqualTo("a expected:<b> but was:<null>");
    }
}

