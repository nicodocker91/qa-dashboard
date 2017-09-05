<?php
declare(strict_types = 1);

namespace Dashboard\Infrastructure;

/**
 * Class SyntaxHighlighter
 *
 * This class manages highlighted syntax code output in HTML with dealing with PHP opening tags.
 * @author Nicolas Giraud <nicolas.giraud.dev@gmail.com>
 */
class SyntaxHighlighter
{
    /**
     * Highlights without nasty PHP opening tag.
     *
     * @param string $phpCode The PHP snippet to highlight.
     * @return string
     */
    public static function highlight(string $phpCode): string
    {
        // Use internal PHP function to highlight but this requires unwanted PHP opening tag.
        $phpCode = \highlight_string('<?php' . \PHP_EOL . \trim($phpCode), true);

        $phpOpenTagRegEx = '/(<span style="color: #[a-fA-F0-9]{0,6}">)(&lt;\?php(?:&nbsp;|<br \/>))(.*?)(<\/span>)/';
        $phpCode = \preg_replace($phpOpenTagRegEx, '$1$3$4', \trim($phpCode));

        return \trim($phpCode);
    }
}
