<?php

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return \DokanElementor
 */
function dokan_elementor() {
    return dokan_pro()->module->elementor;
}
