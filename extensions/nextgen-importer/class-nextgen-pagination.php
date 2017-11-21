<?php
/**
 * Pagination Class. Adapted from http://www.mis-algoritmos.com/2007/05/27/digg-style-pagination-class/
 * Original author: Victor De la Rocha
 * Date: 28/11/2015
 */
if ( ! class_exists( 'FooGalleryNextGenPagination' ) ) {

	class FooGalleryNextGenPagination {

		/* Default values */
		var $item_count = 100;
		var $limit = 10;
		var $target = "";
		var $page = 1;
		var $adjacents = 2;
		var $parameterName = "paged";
		var $urlF = false;//urlFriendly
		var $start = -1;
		var $end = -1;
		var $page_count = 10;
		var $url = false;

		/* Next and previous buttons */
		var $nextI = "›";
		var $prevI = "‹";

		/* Processing variables */
		var $calculate = false;
		var $pagination = '';
		var $errors = false;

		#Total items
		function items( $value ) {
			$this->item_count = (int) $value;
		}

		#how many items to show per page
		function limit( $value ) {
			$this->limit = (int) $value;
		}

		#Page to sent the page value
		function target( $value ) {
			$this->target = $value;
		}

		#Current page
		function currentPage( $value ) {
			$this->page = (int) $value;
		}

		#How many adjacent pages should be shown on each side of the current page?
		function adjacents( $value ) {
			$this->adjacents = (int) $value;
		}

		#show counter?
		function showCounter( $value = "" ) {
			$this->showCounter = ( $value === true ) ? true : false;
		}

		#to change the class name of the pagination div
		function changeClass( $value = "" ) {
			$this->className = $value;
		}

		function nextLabel( $value ) {
			$this->nextT = $value;
		}

		function nextIcon( $value ) {
			$this->nextI = $value;
		}

		function prevLabel( $value ) {
			$this->prevT = $value;
		}

		function prevIcon( $value ) {
			$this->prevI = $value;
		}

		#to change the class name of the pagination div
		function parameterName( $value = "" ) {
			$this->parameterName = $value;
		}

		function render($echo = true) {
			if ( ! $this->calculate ) {
				$this->calculate();
			}
			if ( $echo ) {
				echo $this->pagination;
			} else {
				return $this->pagination;
			}
		}

		function get_page_number_url( $page_number ) {
			return esc_url( add_query_arg( $this->parameterName, $page_number, $this->url ) );
		}

		function calculate() {
			$this->pagination = "";
			$this->calculate = true;
			$this->errors = false;

			if ( $this->urlF and $this->urlF != '%' and strpos( $this->target, $this->urlF ) === false ) {
				//Es necesario especificar el comodin para sustituir
				$this->errors = "Especificaste un wildcard para sustituir, pero no existe en el target";
				return;
			} elseif ( $this->urlF and $this->urlF == '%' and strpos( $this->target, $this->urlF ) === false ) {
				$this->errors = "Es necesario especificar en el target el comodin % para sustituir el n�mero de p�gina";
				return;
			}

			$n = $this->nextI;
			$p = $this->prevI;

			/* Setup vars for query. */
			if ( $this->page ) {
				$this->start = ( $this->page - 1 ) * $this->limit; //first item to display on this page
				$this->end = $this->start + $this->limit - 1;
			} else {
				$this->start = 0; //if no page var is given, set start to 0
				$this->end = $this->limit - 1;
			}

			/* Setup page vars for display. */
			$counter  = 1;
			$prev     = $this->page - 1;                            //previous page is page - 1
			$next     = $this->page + 1;                            //next page is page + 1
			$this->page_count = ceil( $this->item_count / $this->limit );        //lastpage is = total pages / items per page, rounded up.
			$lpm1     = $this->page_count - 1;                        //last page minus 1

			/*
				Now we apply our rules and draw the pagination object.
				We're actually saving the code to a variable in case we want to draw it more than once.
			*/

			if ( $this->page_count > 1 ) {
				if ( $this->page ) {
					//anterior button
					if ( $this->page > 1 ) {
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( $prev ) . "\" class=\"prev\">$p</a>";
					} else {
						$this->pagination .= "<span class=\"disabled\">$p</span>";
					}
				}
				//pages
				if ( $this->page_count < 7 + ( $this->adjacents * 2 ) ) {//not enough pages to bother breaking it up
					for ( $counter = 1; $counter <= $this->page_count; $counter ++ ) {
						if ( $counter == $this->page ) {
							$this->pagination .= "<span class=\"selected-page\">$counter</span>";
						} else {
							$this->pagination .= "<a href=\"" . $this->get_page_number_url( $counter ) . "\">$counter</a>";
						}
					}
				} elseif ( $this->page_count > 5 + ( $this->adjacents * 2 ) ) {//enough pages to hide some
					//close to beginning; only hide later pages
					if ( $this->page < 1 + ( $this->adjacents * 2 ) ) {
						for ( $counter = 1; $counter < 4 + ( $this->adjacents * 2 ); $counter ++ ) {
							if ( $counter == $this->page ) {
								$this->pagination .= "<span class=\"selected-page\">$counter</span>";
							} else {
								$this->pagination .= "<a href=\"" . $this->get_page_number_url( $counter ) . "\">$counter</a>";
							}
						}
						$this->pagination .= "...";
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( $lpm1 ) . "\">$lpm1</a>";
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( $this->page_count ) . "\">$this->page_count</a>";
					} //in middle; hide some front and some back
					elseif ( $this->page_count - ( $this->adjacents * 2 ) > $this->page && $this->page > ( $this->adjacents * 2 ) ) {
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( 1 ) . "\">1</a>";
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( 2 ) . "\">2</a>";
						$this->pagination .= "...";
						for ( $counter = $this->page - $this->adjacents; $counter <= $this->page + $this->adjacents; $counter ++ ) {
							if ( $counter == $this->page ) {
								$this->pagination .= "<span class=\"selected-page\">$counter</span>";
							} else {
								$this->pagination .= "<a href=\"" . $this->get_page_number_url( $counter ) . "\">$counter</a>";
							}
						}
						$this->pagination .= "...";
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( $lpm1 ) . "\">$lpm1</a>";
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( $this->page_count ) . "\">$this->page_count</a>";
					} //close to end; only hide early pages
					else {
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( 1 ) . "\">1</a>";
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( 2 ) . "\">2</a>";
						$this->pagination .= "...";
						for ( $counter = $this->page_count - ( 2 + ( $this->adjacents * 2 ) ); $counter <= $this->page_count; $counter ++ ) {
							if ( $counter == $this->page ) {
								$this->pagination .= "<span class=\"selected-page\">$counter</span>";
							} else {
								$this->pagination .= "<a href=\"" . $this->get_page_number_url( $counter ) . "\">$counter</a>";
							}
						}
					}
				}
				if ( $this->page ) {
					//siguiente button
					if ( $this->page < $counter - 1 ) {
						$this->pagination .= "<a href=\"" . $this->get_page_number_url( $next ) . "\" class=\"next\">$n</a>";
					} else {
						$this->pagination .= "<span class=\"disabled\">$n</span>";
					}
				}
			}
		}
	}
}