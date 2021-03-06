<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Doozr - Form - Service.
 *
 * File.php - Extension to default Input-Component <input type="..." ...
 * but with some specific file-upload specific.
 *
 * PHP versions 5.5
 *
 * LICENSE:
 * Doozr - The lightweight PHP-Framework for high-performance websites
 *
 * Copyright (c) 2005 - 2016, Benjamin Carl - All rights reserved.
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
 *   must display the following acknowledgment: This product includes software
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
 * @category   Doozr
 *
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2016 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * @version    Git: $Id$
 *
 * @link       http://clickalicious.github.com/Doozr/
 */
require_once DOOZR_DOCUMENT_ROOT.'Service/Doozr/Form/Service/Component/Input.php';
require_once DOOZR_DOCUMENT_ROOT.'Service/Doozr/Form/Service/Component/Interface/File.php';

/**
 * Doozr - Form - Service.
 *
 * Extension to default Input-Component <input type="..." ...
 * but with some specific file-upload specific.
 *
 * @category   Doozr
 *
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2016 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 * @version    Git: $Id$
 *
 * @link       http://clickalicious.github.com/Doozr/
 */
class Doozr_Form_Service_Component_File extends Doozr_Form_Service_Component_Input
    implements
    Doozr_Form_Service_Component_Interface_File
{
    /**
     * The maximum filesize allowed for file-uploads in Bytes.
     *
     * @var int
     */
    protected $maxFilesize = 0;

    /**
     * The filename of this component.
     *
     * @var string
     */
    protected $file;

    /*------------------------------------------------------------------------------------------------------------------
    | PUBLIC API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Constructor.
     *
     * @param Doozr_Form_Service_Renderer_Interface  $renderer  Renderer instance for rendering this component
     * @param Doozr_Form_Service_Validator_Interface $validator Validator instance for validating this component
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function __construct(
        Doozr_Form_Service_Renderer_Interface  $renderer  = null,
        Doozr_Form_Service_Validator_Interface $validator = null
    ) {
        $this->setType(Doozr_Form_Service_Constant::COMPONENT_TYPE_FILE);

        // Important call so observer storage ... can be initiated
        parent::__construct($renderer, $validator);
    }

    /**
     * Setter for accept.
     *
     * @param string $mimeType The mime type to set.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setAccept($mimeType)
    {
        $this->setAttribute('accept', $mimeType);
    }

    /**
     * Getter for accept.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return string The accept attributes value
     */
    public function getAccept()
    {
        return $this->getAttribute('accept');
    }

    /**
     * Setter for type.
     *
     * @param string $type The type to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setType($type)
    {
        $this->type = $type;

        parent::setType($type);
    }

    /**
     * Setter for value.
     *
     * @param mixed $value The value to set
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setValue($value)
    {
        $this->setFile($value);
    }

    /**
     * Getter for value.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return mixed Value of this element
     */
    public function getValue()
    {
        return $this->getFile();
    }

    /**
     * Setter for file.
     *
     * @param string|null $file The file
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setFile($file = null)
    {
        $this->file = $file;
    }

    /**
     * Getter for file.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return string|null The filename if set, otherwise NULL
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets the maximum size in bytes a file is allowed to have.
     *
     * @param int|string $maxFilesize The maximum allowed size in bytes
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return int The size in bytes
     */
    public function setMaxFilesize($maxFilesize = 'auto')
    {
        switch ($maxFilesize) {
            case 'auto':
                $maxFilesize = $this->convertToBytes(
                    ini_get('upload_max_filesize')
                );
                break;
        }

        $this->maxFilesize = $maxFilesize;
        $this->setAttribute('filesize', $maxFilesize);
    }

    /**
     * Returns the maximum size in bytes a file is allowed to have.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return int The size in bytes
     */
    public function getMaxFilesize()
    {
        return $this->maxFilesize;
    }

    /*-----------------------------------------------------------------------------------------------------------------+
    | Tools & Helper
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Converts an PHP INI-Value from string to integer (bytes).
     *
     * @param string $value The value to convert
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return int The value in bytes
     */
    protected function convertToBytes($value)
    {
        $value = trim($value);
        $last  = strtolower($value[strlen($value) - 1]);

        switch ($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $value *= 1024;
                break;

            case 'm':
                $value *= 1024;
                break;

            case 'k':
                $value *= 1024;
                break;
        }

        return $value;
    }
}
