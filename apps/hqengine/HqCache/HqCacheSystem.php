<?php

namespace HqEngine\HqCache;

use Phalcon\Cache\Backend;

class HqCacheSystem {

    const
    /**
     * Cache key for router data.
     */
            CACHE_KEY_ROUTER_DATA = 'hq_router_data',
            /**
             * Widgets metadata, stored from modules.
             */
            CACHE_KEY_WIDGETS_METADATA = 'hq_widgets_metadata';

}
