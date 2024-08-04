<?php
/**
 * Smarty form block function.
 *
 * @package ow.ow_smarty.plugin
 * @since 1.0
 */
function smarty_block_form( $params, $content )
{
    if ( !isset($params['name']) )
    {
        throw new InvalidArgumentException('Empty form name!');
    }

    $vr = OW_ViewRenderer::getInstance();
    
    $assignedForms = $vr->getAssignedVar('_owForms_');
    
    if ( !isset($assignedForms[$params['name']]) )
    {
        throw new InvalidArgumentException('There is no form with name `' . $params['name'] . '` !');
    }

    // mark active form
    if ( $content === null )
    {
        $vr->assignVar('_owActiveForm_', $assignedForms[$params['name']]);
        return;
    }

    /* @var $form OW_Form */
    $form = $vr->getAssignedVar('_owActiveForm_');

    if ( isset($params['decorator']) )
    {
        $viewRenderer = OW_ViewRenderer::getInstance();
        $viewRenderer->assignVar('formInfo', $form->getElementsInfo());
        $content = $viewRenderer->renderTemplate(OW::getThemeManager()->getDecorator($params['decorator']));
    }

    unset($params['decorator']);
    unset($params['name']);
    return $form->render($content, $params);
}