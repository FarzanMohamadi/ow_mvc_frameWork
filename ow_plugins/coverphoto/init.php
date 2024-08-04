<?php

OW::getRouter()->addRoute(new OW_Route('coverphoto-index', 'coverphoto/upload', 'COVERPHOTO_CTRL_Forms', 'upload'));
OW::getRouter()->addRoute(new OW_Route('coverphoto-admin', 'coverphoto/admin', 'COVERPHOTO_CTRL_Admin', 'index'));

OW::getRouter()->addRoute(new OW_Route('coverphoto-forms-delete-item', 'coverphoto/forms/delete/:id', 'COVERPHOTO_CTRL_Forms', 'deleteItem'));
OW::getRouter()->addRoute(new OW_Route('coverphoto-forms-use-item', 'coverphoto/forms/use/:id', 'COVERPHOTO_CTRL_Forms', 'useItem'));
OW::getRouter()->addRoute(new OW_Route('coverphoto-forms-cover-crop', 'coverphoto/forms/cover/crop/:id', 'COVERPHOTO_CTRL_Forms', 'coverCrop'));
