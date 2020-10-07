/**
 * FormResults
 *
 * Form results
 *
 * @category    module
 * @version     0.1.0
 * @author      mnoskov
 * @internal    @guid formresults
 * @internal    @modx_category Manager and Admin
 */
//<?php

require_once MODX_BASE_PATH . 'assets/modules/formresults/core/src/FormResults.php';

if (!$modx->hasPermission('exec_module')) {
    $modx->sendRedirect('index.php?a=106');
}

if (!is_array($modx->event->params)) {
    $modx->event->params = [];
}

(new FormResults($modx->event->params))->processRequest();
