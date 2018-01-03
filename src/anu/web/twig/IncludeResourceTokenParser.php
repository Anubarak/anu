<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 13:58
 */

namespace anu\web\twig;

class IncludeResourceTokenParser  extends  \Twig_TokenParser{
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    private $_tag;

    /**
     * @var boolean
     */
    private $_allowTagPair;

    // Public Methods
    // =========================================================================

    /**
     * Constructor
     *
     * @param string $tag
     *
     */
    public function __construct($tag, $allowTagPair = false)
    {
        $this->_tag = $tag;
        $this->_allowTagPair = $allowTagPair;
    }

    /**
     * Parses resource include tags.
     *
     * @param \Twig_Token $token
     *
     * @return IncludeResource_Node
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Syntax
     */
    public function parse(\Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        if ($this->_allowTagPair && ($stream->test(\Twig_Token::NAME_TYPE, 'first') || $stream->test(\Twig_Token::BLOCK_END_TYPE)))
        {
            $capture = true;

            $first = $this->_getFirstToken($stream);
            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
            $value = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        }
        else
        {
            $capture = false;

            $value = $this->parser->getExpressionParser()->parseExpression();
            $first = $this->_getFirstToken($stream);
            $stream->expect(\Twig_Token::BLOCK_END_TYPE);
        }

        $nodes = array(
            'value' => $value,
        );

        $attributes = array(
            'function' => $this->_tag,
            'capture'  => $capture,
            'first'    => $first,
        );

        return new IncludeResource_Node($nodes, $attributes, $lineno, $this->getTag());
    }

    public function decideBlockEnd(\Twig_Token $token)
    {
        return $token->test('end'.strtolower($this->_tag));
    }

    /**
     * Defines the tag name.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->_tag;
    }

    // Private Methods
    // =========================================================================

    private function _getFirstToken($stream)
    {
        $first = $stream->test(\Twig_Token::NAME_TYPE, 'first');

        if ($first)
        {
            $stream->next();
        }

        return $first;
    }
}

class IncludeResource_Node extends \Twig_Node
{
    // Public Methods
    // =========================================================================

    /**
     * Compiles an IncludeResource_Node into PHP.
     *
     * @param \Twig_Compiler $compiler
     *
     * @return null
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $function = $this->getAttribute('function');
        $value = $this->getNode('value');

        $compiler
            ->addDebugInfo($this);

        if ($this->getAttribute('capture'))
        {
            $compiler
                ->write("ob_start();\n")
                ->subcompile($value)
                ->write("\$_js = ob_get_clean();\n")
            ;
        }
        else
        {
            $compiler
                ->write("\$_js = ")
                ->subcompile($value)
                ->raw(";\n")
            ;
        }

        $compiler
            ->write("\\Anu\\anu()->template->{$function}(\$_js")
        ;

        if ($this->getAttribute('first'))
        {
            $compiler->raw(', true');
        }

        $compiler->raw(");\n");
    }
}