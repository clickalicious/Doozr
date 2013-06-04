<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * DoozR - Index - View
 *
 * Index.php - Index View Demonstration
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
 * @package    DoozR_Index
 * @subpackage DoozR_Index_View
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2013 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/DoozR/
 * @see        -
 * @since      -
 */

/**
 * DoozR - Index - View
 *
 * Index View Demonstration
 *
 * @category   DoozR
 * @package    DoozR_Index
 * @subpackage DoozR_Index_View
 * @author     Benjamin Carl <opensource@clickalicious.de>
 * @copyright  2005 - 2013 Benjamin Carl
 * @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @version    Git: $Id$
 * @link       http://clickalicious.github.com/DoozR/
 * @see        -
 * @since      -
 */
final class View_Index extends DoozR_Base_View implements DoozR_Base_View_Interface
{
    /**
     * This method is the replacement for construct. It is called right on construction of
     * the class-instance. It retrieves all arguments 1:1 as passed to constructor.
     *
     * @param array $request     The original request
     * @param array $translation The translation to read the request
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    protected function __tearup(array $request, array $translation)
    {
        pre(
            '__tearup() in '.__CLASS__.' called! :: '.__CLASS__.' start processing of: '.var_export($request, true).
            ' translation: '.var_export($translation, true)
        );

        /******/
        /* @var $model DoozR_Model */
        /*
        $model = DoozR_Model::getInstance();

        // get connection
        $connection = $model->phpillowConnection->___createInstance('localhost', 5984);

        // set database
        $model->phpillowConnection->setDatabase('doozr');

        // get base structure/object for documents
        require_once 'App/myBlogDocument.php';

        // create new doc
        $doc = new myBlogDocument();
        $doc->title = 'New blog post 2';
        $doc->text = 'Hello world.';
        $doc->save();
        */
        /******/
    }

    /**
     * This method is the replacement for construct. It is called right on construction of
     * the class-instance. It retrieves all arguments 1:1 as passed to constructor.
     *
     * @author Benjamin Carl <opensource@clickalicious.de>
     * @return void
     * @access protected
     */
    public function __teardown()
    {
        pre(
            '__teardown() in '.__CLASS__.' called! :: '.__CLASS__
        );
    }
}

?>