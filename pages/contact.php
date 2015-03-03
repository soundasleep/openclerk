<?php

use \Pages\PageRenderer;

PageRenderer::header(array("title" => t("Contact"), "id" => "page_contact"));
PageRenderer::requireTemplate("contact");
PageRenderer::footer();
