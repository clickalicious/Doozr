<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DoozR - Form - Service
 *
 * Abstract.php - Abstract Renderer. Brings basic templating and rendering
 * capabilities to the rendering part of the service.
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

require_once DOOZR_DOCUMENT_ROOT . 'Service/DoozR/Form/Service/Renderer/Interface.php';

/**
 * DoozR - Form - Service
 *
 * Abstract Renderer. Brings basic templating and rendering
 * capabilities to the rendering part of the service.
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
abstract class DoozR_Form_Service_Renderer_Abstract
    implements
    DoozR_Form_Service_Renderer_Interface
{
    /**
     * Default template for rendering
     *
     * @var $array
     * @access protected
     */
    protected $template = array();

    /**
     * The raw buffer content we operate on
     *
     * @var string
     * @access protected
     */
    protected $buffer;


    /*------------------------------------------------------------------------------------------------------------------
     | Public API
     +----------------------------------------------------------------------------------------------------------------*/

    /**
     * Renders a passed templates with variables childs and attributes
     *
     * @param boolean $force      TRUE to force rerendering, otherwise FALSE to do not
     * @param array   $template   The template to render
     * @param string  $tag        The tag of the component
     * @param array   $variables  The variables to use for rendering
     * @param array   $childs     The child elements
     * @param array   $attributes The attributes
     * @param string  $innerHtml  The inner Html to set
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
        $innerHtml        = ''
    ) {
        // What is with
        $template['attributes'] = array();
        foreach ($attributes as $key => $attribute) {
            $template['attributes'][$key] = $attribute;
        }

        $template['childs'] = array();
        foreach ($childs as $child) {
            $template['childs'][] = $child;
        }

        $template['variables'] = array();
        foreach ($variables as $key => $variable) {
            $template['variables'][$key] = $variable;
        }

        return $template;
    }

    /*-----------------------------------------------------------------------------------------------------------------*
    | Tools & Helper
    *-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Micro templating engine used for processing templates of tags for example.
     *
     * @param string $template          The template to use
     * @param array  $templateVariables The variables used for replace
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string The result
     * @access protected
     */
    protected function _tpl($template, array $templateVariables)
    {
        // micro templating engine
        foreach ($templateVariables as $templateVariable => $value) {
            $template = str_replace('{{'.strtoupper($templateVariable).'}}', $value, $template);
        }

        return $template;
    }
}
