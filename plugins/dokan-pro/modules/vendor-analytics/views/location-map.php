<div id="dokan-vendor-analytics-location-map" style="height: 340px"></div>

<table class="table table-striped">
    <thead>
        <tr>
            <?php foreach( $headers as $header ): ?>
                <th><?php echo esc_html( $header ); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach( $rows as $row ): ?>
            <tr>
                <?php
                foreach ( $row as $key => $column) {
                    switch ( $results['columnHeaders'][$key]->getName() ) {
                        case 'ga:avgTimeOnPage':
                            $column = round( $column, 2 );
                            break;
                        case 'ga:entranceRate':
                            $column = round( $column, 2 ) . '%';
                            break;
                        case 'ga:exitRate':
                            $column = round( $column, 2 ) . '%';
                            break;
                    }
                    echo '<td>' . $column . '</td>';
                } ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
