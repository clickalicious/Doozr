<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DoozR - Form - Service
 *
 * Abstract.php - Abstract base for parser.
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

/**
 * DoozR - Form - Service
 *
 * Abstract base for parser.
 *
 * @category   DoozR
 * @package    DoozR_Service
 * @subpackage DoozR_Service_Form
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2013 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id: 1273acd716766791d2770bfe0bd9f1d161a7d047 $
 * @link       http://clickalicious.github.com/DoozR/
 */
abstract class DoozR_Form_Service_Parser_Abstract
{
    /**
     * The input which get parsed by parser.
     *
     * @var string
     * @access protected
     */
    protected $input;

    /**
     * The output returned by parser as result.
     *
     * @var mixed
     * @access protected
     */
    protected $output;

    /**
     * Configuration object.
     *
     * @var DoozR_Form_Service_Configuration
     * @access protected
     */
    protected $configuration;

    /*------------------------------------------------------------------------------------------------------------------
    | Public API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Constructor.
     *
     * @param DoozR_Form_Service_Configuration $configuration The configuration object which is used to store and return
     *                                                        parsed configuration.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return \DoozR_Form_Service_Parser_Abstract
     * @access protected
     */
    public function __construct(DoozR_Form_Service_Configuration $configuration)
    {
        $this->setConfiguration($configuration);
    }

    /**
     * Setter for configuration.
     *
     * @param DoozR_Form_Service_Configuration $configuration The configuration to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access public
     */
    public function setConfiguration(DoozR_Form_Service_Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Getter for configuration.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return DoozR_Form_Service_Configuration|null Configuration if set, otherwise NULL
     * @access public
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set input for parser.
     *
     * @param string $input The input to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access public
     */
    public function setInput($input)
    {
        $this->input = $input;
    }

    /**
     * Getter for input.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string|null The input set, otherwise NULL if not set
     * @access public
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * Setter for output.
     *
     * @param string $output The output to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access public
     */
    public function setOutput($output)
    {
        $this->output = $output;
    }

    /**
     * Getter for output.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return string|null The output set, otherwise NULL if not set
     * @access public
     */
    public function getOutput()
    {
        return $this->output;
    }
}
