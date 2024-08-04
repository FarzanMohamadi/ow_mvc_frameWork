<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */

OW::getRouter()->addRoute(new OW_Route('frmeconetdanesh.admin', 'frmeconetdanesh/admin', 'FRMECONETDANESH_CTRL_Admin', 'index'));

OW::getRouter()->addRoute(new OW_Route('frmeconetdanesh.tags.widget', 'danesh/tags-widget', "FRMECONETDANESH_CTRL_Danesh", 'tagsWidget'));
