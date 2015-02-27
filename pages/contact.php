<?php

use \Pages\PageRenderer;

PageRenderer::header(array("title" => "Contact", "id" => "page_contact", "js" => "accounts"));
PageRenderer::requireTemplate("contact");
PageRenderer::footer();
