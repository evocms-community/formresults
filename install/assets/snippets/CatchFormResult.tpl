/**
 * CatchFormResult
 *
 * Catches form result and saves it in database
 *
 * @category    snippet
 * @version     0.1.0
 * @author      mnoskov
 * @internal    @modx_category Manager and Admin
 */

//<?php
require_once MODX_BASE_PATH . 'assets/modules/formresults/core/src/FormResults.php';
return (new FormResults())->catchFormResult($data, $FormLister);
