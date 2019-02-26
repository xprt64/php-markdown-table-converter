<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Constantin Galbenu <xprt64@gmail.com>
 */

declare(strict_types=1);

use League\HTMLToMarkdown\Environment;
use League\HTMLToMarkdown\HtmlConverter;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../TableConverter.php';

class TableConverterTest extends TestCase
{
    protected function setUp(): void
    {
        error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
        parent::setUp();
    }

    /**
     * @dataProvider tablesProvider
     */
    public function testTable($input, $expected)
    {
        $environment = new Environment();
        $environment->addConverter(new TableConverter());

        $markdown = new HtmlConverter($environment);

        $actual = $markdown->convert($input);
        $this->assertSame($expected, $actual);
    }

    public function tablesProvider()
    {
        return [
            [
                <<<'TAG'
<table>
    <thead>
        <tr>
            <td>col1</td>
            <td>col2</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>aaa</td>
            <td>bbb</td>
        </tr>
    </tbody>
</table>
TAG
                , <<<'TAG'
| col1 | col2 |
|------|------|
| aaa | bbb |
TAG
                ,
            ],
        [ /** two rows */
                <<<'TAG'
<table>
    <thead>
        <tr>
            <td>col1</td>
            <td>col2</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>aaa</td>
            <td>bbb</td>
        </tr>
        <tr>
            <td>ccc</td>
            <td>ddd</td>
        </tr>
    </tbody>
</table>
TAG
                , <<<'TAG'
| col1 | col2 |
|------|------|
| aaa | bbb |
| ccc | ddd |
TAG
                ,
            ],
           [ /** no thead/tbody */
                <<<'TAG'
<table>
        <tr>
            <td>col1</td>
            <td>col2</td>
        </tr>
        <tr>
            <td>aaa</td>
            <td>bbb</td>
        </tr>
</table>
TAG
                , <<<'TAG'
| col1 | col2 |
|------|------|
| aaa | bbb |
TAG
                ,
            ],
        ];
    }
}
