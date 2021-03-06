<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Doozr - Form - Service.
 *
 * Keygen.php - The Kegen component.
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

/**
 * Doozr - Form - Service.
 *
 * The keygen component.
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
class Doozr_Form_Service_Component_Keygen extends Doozr_Form_Service_Component_Input
{
    /**
     * The tag of this component.
     *
     * @var string
     */
    protected $tag = Doozr_Form_Service_Constant::HTML_TAG_KEYGEN;

    /**
     * The allowed keytypes for the component.
     *
     * @var array
     */
    protected $allowedKeytypes = [
        self::KEYTYPE_RSA,
        self::KEYTYPE_DSA,
        self::KEYTYPE_EC,
    ];

    /**
     * Keytype RSA.
     */
    const KEYTYPE_RSA = 'rsa';

    /**
     * Keytype DSA.
     */
    const KEYTYPE_DSA = 'dsa';

    /**
     * Keytype EC.
     */
    const KEYTYPE_EC = 'ec';

    /*------------------------------------------------------------------------------------------------------------------
    | PUBLIC API
    +-----------------------------------------------------------------------------------------------------------------*/

    /**
     * Setter for challenge.
     *
     * @param string $challenge The challenge
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     */
    public function setChallenge($challenge)
    {
        $this->setAttribute('challenge', $challenge);
    }

    /**
     * Getter for challenge.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return string The challenge
     */
    public function getChallenge()
    {
        return $this->getAttribute('challenge');
    }

    /**
     * Setter for keytype.
     *
     * @param string $keytype The keytype
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @throws Doozr_Form_Service_Exception
     */
    public function setKeytype($keytype)
    {
        if (in_array($keytype, $this->allowedKeytypes) === false) {
            throw new Doozr_Form_Service_Exception(
                'Passed keytype: "'.$keytype.'" is not allowed or invalid.'
            );
        }

        $this->setAttribute('keytype', $keytype);
    }

    /**
     * Getter for keytype.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     *
     * @return string The keytype
     */
    public function getKeytype()
    {
        return $this->getAttribute('keytype');
    }
}
