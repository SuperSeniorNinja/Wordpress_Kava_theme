<?php

namespace Texty\Notifications\Dokan;

class ProcessingVendor extends Base {

    /**
     * Initialize
     */
    public function __construct() {
        $this->title = __( 'Vendor - When Order Status is Processing', 'texty' );
        $this->id    = 'order_dokan_processing';
        $this->group = 'dokan';
        $this->type  = 'vendor';

        $this->default = <<<'EOD'
New order received #{order_id}, paid via {payment_method}.

{items}

Customer: {billing_name} ({billing_email})
Status: {status}
Order Total: {order_total}
EOD;
    }
}
