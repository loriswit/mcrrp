<?php

use League\CommonMark\ElementRendererInterface;
use League\CommonMark\HtmlElement;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;

/**
 * Markdown renderer for web links
 */
class LinkRenderer implements InlineRendererInterface
{
    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if(!($inline instanceof Link))
            throw new InvalidArgumentException("Expected League\CommonMark\Inline\Element\Link, got ".get_class($inline));
        
        $attr = [
            "class" => "link",
            "href" => $inline->getUrl(),
        ];
        $content = $htmlRenderer->renderInlines($inline->children());
        
        return new HtmlElement("a", $attr, $content);
    }
}
