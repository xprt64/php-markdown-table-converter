<?php
/**
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Constantin Galbenu <xprt64@gmail.com>
 * @author Chris Kruining <chris@gmailkruining.eu>
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
    public function convert(ElementInterface $element)
    {
        switch ($element->getTagName()) {
            case 'tr':
                return sprintf(
                    "| %s |\n",
                    implode(' | ', array_map(function($td){ return trim($td->getValue()); }, $element->getChildren()))
                );

            case 'td':
            case 'th':
                return preg_replace("#\n+#", '\n', trim($element->getValue()));

            case 'tbody':
                return trim($element->getValue());

            case 'thead':
                $children = $element->getChildren();
                $headerLine = reset($children)->getValue();
                $headers = explode(' | ', trim(trim($headerLine, "\n"), '|'));

                $hr = [];
                foreach ($headers as $td) {
                    $length = strlen(trim($td)) + 2;
                    $hr[] = str_repeat('-', $length > 3 ? $length : 3);
                }
                $hr = '|' . implode('|', $hr) . '|';

                return $headerLine . $hr . "\n";
            case 'table':
                $inner = $element->getValue();
                $data = array_map(
                    function($r){
                        return array_slice(array_map(
                            function($r){ return preg_match('/\-+/', $r) ? '-' : trim($r); },
                            explode('|', $r)
                        ), 1, -1);
                    },
                    explode("\n", $inner)
                );
                $size = count($data[0]);

                for($i = 0; $i < $size; $i++)
                {
                    $width = max(array_map(function($r) use($i){ return mb_strlen($r[$i] ?? ''); }, $data));

                    foreach($data as &$row)
                    {
                        if($width < 2)
                        {
                            unset($row[$i]);
                        }
                        else
                        {
                            $cell = $row[$i] ?? '';

                            $format = $cell === '-'
                                ? '%\'-' . $width . 's'
                                : '%-' . $width . 's';

                            $row[$i] = mb_sprintf($format, $cell);
                        }
                    }

                    unset($row);
                }

                $inner = join("\n", array_map(function($r){ return sprintf('| %s |', join(' | ', $r)); }, $data));
                return trim($inner) . "\n\n";
        }

        return $element->getValue();
    }

    /**
     * @return string[]
     */
    public function getSupportedTags()
    {
        return array('table', 'thead', 'tbody', 'tfoot', 'tr', 'td', 'th');
    }
}
