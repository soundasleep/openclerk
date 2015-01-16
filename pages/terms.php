<?php

use \Pages\PageRenderer;

PageRenderer::header(array(
  "title" => t("Terms of Service"),
  "id" => "page_terms",
));
PageRenderer::requireTemplate("terms");
PageRenderer::footer();
