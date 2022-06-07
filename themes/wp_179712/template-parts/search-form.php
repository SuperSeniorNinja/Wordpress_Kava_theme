<div class="rd-navbar-search rd-navbar-search-toggled">
	<button class="rd-navbar-search-toggle" data-rd-navbar-toggle=".rd-navbar-search"></button>
	<form role="search" method="get" class="rd-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <div class="form-wrap">
            <input class="form-input" id="rd-navbar-search-form-input" type="search" name="s" value="<?php echo get_search_query() ?>" placeholder="<?php echo esc_attr_x( 'Search...', 'placeholder', 'kava' ) ?>">
        </div>
        <button type="submit" class="rd-navbar-search-submit"></button>
    </form>
</div>