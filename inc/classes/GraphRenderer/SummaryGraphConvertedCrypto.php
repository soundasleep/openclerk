<?php

/**
 * Simply replaces the title.
 */
class GraphRenderer_SummaryGraphConvertedCrypto extends GraphRenderer_SummaryGraph {

  function getTitle() {
    return ct("Converted :currency");
  }

}
