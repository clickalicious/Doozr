<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DoozR - Form - Service
 *
 * Html.php - HTML Renderer. Renders a components setup to HTML.
 *
 * PHP versions 5
 *
 * LICENSE:
 * DoozR - The PHP-Framework
 *
 * Copyright (c) 2005 - 2013, Benjamin Carl - All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * - Redistributions of source code must retain the above copyright notice,
 *   this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 * - All advertising materials mentioning features or use of this software
 *   must display the following acknowledgement: This product includes software
 *   developed by Benjamin Carl and other contributors.
 * - Neither the name Benjamin Carl nor the names of other contributors
 *   may be used to endorse or promote products derived from this
 *   software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * Please feel free to contact us via e-mail: opensource@clickalicious.de
 *
 * @category   DoozR
 * @package    DoozR_Service
 * @subpackage DoozR_Service_Form
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2013 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/DoozR/
 */

require_once DOOZR_DOCUMENT_ROOT . 'Service/DoozR/Form/Service/Renderer/Abstract.php';

/**
 * DoozR - Form - Service
 *
 * HTML Renderer. Renders a components setup to HTML.
 *
 * @category   DoozR
 * @package    DoozR_Service
 * @subpackage DoozR_Service_Form
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2013 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id:$
 * @link       http://clickalicious.github.com/DoozR/
 */
class DoozR_Form_Service_Renderer_Html extends DoozR_Form_Service_Renderer_Abstract
{
    /**
     * The components rendered HTML runtime cache
     *
     * @var string
     * @access protected
     */
    protected $html = '';


    /*-----------------------------------------------------------------------------------------------------------------+
    | Public API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Renders a passed templates with variables childs and attributes.
     *
     * @param boolean $force      TRUE to force rerendering, FALSE to accept cached result
     * @param array   $template   The template to render
     * @param string  $tag        The tag of the element to render
     * @param array   $variables  The variables to use for rendering
     * @param array   $childs     The child elements
     * @param array   $attributes The attributes
     * @param string  $innerHtml
     *
     * @internal param $string %innerHtml  The inner HTML of the component
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The rendered result
     * @access public
     */
    public function render(
        $force            = false,
        $template         = array(),
        $tag              = '',
        array $variables  = array(),
        array $childs     = array(),
        array $attributes = array(),
              $innerHtml  = ''
    ) {
        // Render childs if any attached
        if (count($childs) > 0) {
            $innerHtml .= $this->renderChilds($childs, $force);
        }

        // Get attributes prepared as string
        $attributesCollected = $this->prepareAttributes($attributes);

        // Set template variables for our default template
        $templateVariables = array_merge(
            $variables,
            array(
                'attributes' => $attributesCollected,
                'tag'        => $tag
            )
        );

        $html = $this->_tpl($template, $templateVariables);

        // if inner HTML was passed render this as well
        if ($innerHtml !== null) {
            $variables = array(
                'inner-html' => $innerHtml
            );

            $html = $this->_tpl($html, $variables);
        }

        // Return HTML result
        return $html;
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Tools & Helper
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Prepares attributes.
     *
     * @param array $attributes The atributes to prepare
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The rendered result
     * @access public
     */
    protected function prepareAttributes(array $attributes)
    {
        $attributesCollected = '';

        foreach ($attributes as $attribute => $value) {
            // Check value-less attributes to be embedded properly
            if ($value === null) {
                $attributesCollected .= ' ' . $attribute;
            } else {
                $value = (is_array($value)) ? $value[0] : $value;
                $attributesCollected .= ' ' . $attribute . '="' . $value . '"';
            }
        }

        return $attributesCollected;
    }

    /**
     * Renders the HTML of the childs attached
     *
     * @param array   $childs The childs attached
     * @param boolean $force  TRUE to force rerendering, otherwise FALSE to use cache
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The rendered result
     * @access public
     */
    protected function renderChilds(array $childs = array(), $force)
    {
        // The HTML result of childs
        $html = '';

        // Iterate childs and render HTML of each
        foreach ($childs as $child) {
            $html .= $child->render($force);
        }

        return $html;
    }
}
