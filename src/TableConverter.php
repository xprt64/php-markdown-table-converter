<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Constantin Galbenu <xprt64@gmail.com>
 */

/**
 * @see https://github.com/Mark-H/Docs/blob/2.x/convert/util/TableConverter.php
 */

namespace xprt64\HtmlTableToMarkdownConverter;

use League\HTMLToMarkdown\Converter\ConverterInterface;
use League\HTMLToMarkdown\ElementInterface;

class TableConverter implements ConverterInterface
{
    /**
     * @param ElementInterface $element
     *
     * @return string
     */
    public function convert(ElementInterface $element): string
    {
        switch ($element->getTagName()) {
            case 'tr':
                $line = [];
                foreach ($element->getChildren() as $td) {
                    $raw = $td->getValue();
                    if ($raw !== '' && trim($raw) === '') {
                        continue; // whitespace between cells
                    }
                    $line[] = trim($raw);
                }
                return '| ' . implode(' | ', $line) . " |\n";

            case 'td':
            case 'th':
                return preg_replace("#\n+#", '\n', trim($element->getValue()));

            case 'tbody':
                return $this->normalizeRows($element->getValue());

            case 'thead':
                $headerLine = '';
                foreach ($element->getChildren() as $child) {
                    if (trim($child->getValue()) !== '') {
                        $headerLine = trim($child->getValue()) . "\n";
                        break;
                    }
                }
                $headers = explode(' | ', trim(trim($headerLine, "\n"), '|'));

                $hr = [];
                foreach ($headers as $td) {
                    $length = strlen(trim($td)) + 2;
                    $hr[] = str_repeat('-', $length > 3 ? $length : 3);
                }
                $hr = '|' . implode('|', $hr) . '|';

                return $headerLine . $hr . "\n";
            case 'table':
                $inner = $this->normalizeRows($element->getValue());
                if (strpos($inner, '-----') === false) {
                    $inner = explode("\n", $inner);
                    $single = explode(' | ', trim($inner[0], '|'));
                    $hr = [];
                    foreach ($single as $td) {
                        $length = strlen(trim($td)) + 2;
                        $hr[] = str_repeat('-', $length > 3 ? $length : 3);
                    }
                    $hr = '|' . implode('|', $hr) . '|';
                    array_splice($inner, 1, 0, $hr);
                    $inner = implode("\n", $inner);
                }
                return trim($inner) . "\n\n";
        }
        return $element->getValue();
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return array('table', 'tr', 'thead', 'td', 'tbody');
    }

    private function normalizeRows(string $value): string
    {
        $lines = array_filter(array_map('trim', explode("\n", $value)), static fn(string $l) => $l !== '');

        return implode("\n", $lines);
    }
}
