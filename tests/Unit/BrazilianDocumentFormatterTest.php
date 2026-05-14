<?php

namespace Tests\Unit;

use App\Support\BrazilianDocumentFormatter;
use PHPUnit\Framework\TestCase;

class BrazilianDocumentFormatterTest extends TestCase
{
    public function test_it_formats_cpf_rg_and_cep_masks(): void
    {
        $this->assertSame('111.111.111-11', BrazilianDocumentFormatter::cpf('11111111111'));
        $this->assertSame('11.111.111-1', BrazilianDocumentFormatter::rg('111111111'));
        $this->assertSame('99999-999', BrazilianDocumentFormatter::cep('99999999'));
    }
}
