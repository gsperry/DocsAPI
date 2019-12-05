<?php
error_reporting( 0 );
ini_set( 'display_errors', 0 );

//    use Roots\Sage\Extras;
define( 'WP_USE_THEMES', FALSE );
require_once( '../../../wp-load.php' );

// Search server
$search_server = 'http://josh:searchR0cks@104.196.231.217:8764';
//$search_server = 'http://shub.lucidworks.com';

// Reference
/*
line 93 - site search all call

line 331 - site search blog query pipeline

line 378 - site search documentation pipeline

line 415 - site search videos

line 488 - LucidFind default

line 591 - LucidFind default again

line 697 - typeahead test group

line 827 - query similarities
*/

$d_count = rand( 0, 5 );
$b_count = rand( 0, 5 );
$v_count = rand( 0, 5 );
$a_count = rand( 0, 5 );

$retval           = array();
$callback_wrapper = FALSE;
if ( ! empty( $_REQUEST['callback'] ) ) {
	$callback_wrapper = $_REQUEST['callback'];
}
header( 'Content-Type: application/json' );

if ( $_REQUEST['type'] == 'all-search' ) {
	$retval['length'] = 0;

	$query = $_REQUEST['q'];
	if ( trim( $query ) != '' ) {
		$query_parts = explode( ' ', $query );
		// look for count params
		foreach ( $query_parts as $part ) {
			$cat = substr( $part, 0, 2 );
			$num = intval( substr( $part, 2 ) );
			switch ( $cat ) {
				case '-d':
					$d_count = $num;
					break;
				case '-b':
					$b_count = $num;
					break;
				case '-v':
					$v_count = $num;
					break;
				case '-a':
					$a_count = $num;
					break;
			}
		}

		// create the structure
		$result = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/site-search-all/collections/lucidfind/select?&wt=json&q=' . urlencode( $query ) ), TRUE );
		// var_dump($search_server.'/api/apollo/query-pipelines/site-search-all/collections/lucidfind/select?&wt=json&q='.urlencode($query), $result);
		$groups = array();
		// var_dump($result['grouped']['site_search_s']['groups']);
		foreach ( $result['grouped']['site_search_s']['groups'] as $group ) {
			if ( $group['groupValue'] == 'documentation' ) {
				$groups['documentation'] = $group['doclist'];
			} else if ( is_null( $group['groupValue'] ) ) {
				$groups['hub'] = $group['doclist'];
			} else if ( $group['groupValue'] == 'video' ) {
				$groups['videos'] = $group['doclist'];
			} else if ( $group['groupValue'] == 'blog' ) {
				$groups['blog'] = $group['doclist'];
			}
		}


		// documentation
		$lc = array( 'count' => 0, 'results' => array() );
		/*
		for($i = 0; $i < $d_count; $i++) {
		  $temp = array(
			  'url' => 'https://google.com',
			  'title' => 'Query Pipelines',
			  'product' => 'Fusion',
			  'version' => '3.0',
			  'excerpt' => 'Curabitur blandit tempus porttitor. <span class="highlighted">Query</span> pipeline id ligula porta felis euismod Nullam quis risus eget urna mollis ornare vel eu leo Duis mollis, est non commodo luctus'
		  );
		  $lc['results'][] = $temp;
		}
		*/

		// $result = json_decode(file_get_contents('http://staging.searchhub.lucidworks.com/api/apollo/query-pipelines/site-search-documentation/collections/lucidfind/select?fl=productName%20productVersion%20body&hl=on&hl.fl=body%20title&hl.snippets=2&hl.fragsize=300&wt=json&q=' . $query), true);
		if ( ! empty( $groups['documentation'] ) ) {
			$lc['count'] = $groups['documentation']['numFound'];
			foreach ( $groups['documentation']['docs'] as $doc ) {
				$temp            = array(
					'url'     => $doc['id'],
					'title'   => $doc['title'],
					'product' => ! empty( $doc['productName'] ) ? $doc['productName'] : '',
					'version' => ! empty( $doc['productVersion'] ) ? $doc['productVersion'] : ''
				);
				$current_excerpt = '';
				foreach ( $result['highlighting'][ $doc['id'] ] as $name => $section ) {
					foreach ( $section as $item ) {
						if ( strlen( $current_excerpt ) < strlen( trim( $item ) ) ) {
							$current_excerpt = trim( $item );
						}
					}
				}
				$temp['excerpt'] = $current_excerpt;
				$lc['results'][] = $temp;
			}
			if ( $lc['count'] > 0 ) {
				$retval['length'] ++;
			}
			$retval['documentation'] = $lc;
		} else {
			$lc['count']             = 0;
			$retval['documentation'] = $lc;
		}
		// video
		$lc = array( 'count' => 0, 'results' => array() );
		/*
		"https://www.youtube.com/watch?v=g5iLGptfuhg"

		if($v_count > 0) $retval['length']++;
		for($i = 0; $i < $v_count; $i++) {
		  $temp = array(
			  'url' => 'https://google.com',
			  'caption' => 'Aenean <span class="highlighted">query</span> lorem ipsum dolor nulla sed consectetur adipiscing.',
			  'thumbnail' => 'http://lucidworks.restlessdev.com/images/video-frame.jpg'
		  );
		  $lc['results'][] = $temp;
		}
		$lc['count'] = $groups['videos']['numFound'];
		foreach($groups['videos']['docs'] as $doc) {
		  if(substr($doc['id'], 0, strlen("https://www.youtube.com/watch?v=")) == "https://www.youtube.com/watch?v="){
			$temp = array(
			  'url' => $doc['id'],
			  'caption' => $doc['title'],
			  'thumbnail' => 'https://img.youtube.com/vi/'.substr($doc['id'], strpos($doc['id'], '=') + 1).'/hqdefault.jpg'
			);
		  }

		  $lc['results'][] = $temp;
		}
		if($lc['count'] > 0) $retval['length']++;
		$retval['video'] = $lc;
		*/

		// blog
		$lc = array( 'count' => 0, 'results' => array() );
		/*
		if($b_count > 0) $retval['length']++;
		for($i = 0; $i < $b_count; $i++) {
		  $temp = array(
			  'url' => 'https://google.com',
			  'title' => 'Understanding Transaction Logs, Soft Commit and Commit in...',
			  'excerpt' => 'An outline of the consequences hard and soft commits and the new <span class="highlighted">query</span> option for... ',
			  'date' => '2016-11-11',
			  'date_formatted' => 'Nov. 11, 2016',
			  'thumbnail' => 'http://lucidworks.restlessdev.com/images/fpo-blog-1.jpg'
		  );
		  $lc['results'][] = $temp;
		}
		*/
		// $result = json_decode(file_get_contents('http://staging.searchhub.lucidworks.com/api/apollo/query-pipelines/site-search-blog/collections/lucidfind/select?hl=on&hl.fl=content%20title&hl.snippets=2&hl.fragsize=300&wt=json&fl=body.links.targetUri&q=' . $query), true);
		$lc['count'] = ! empty( $groups['blog']['numFound'] ) ? $groups['blog']['numFound'] : 0;
		if ( $lc['count'] > 0 ) {
			foreach ( $groups['blog']['docs'] as $doc ) {
				// var_dump($doc);
				$temp = array(
					'url'            => $doc['id'],
					'title'          => ! empty( $doc['title'] ) ? $doc['title'] : '',
					'date'           => '',
					'date_formatted' => ''
				);
				// get the excerpt
				$current_excerpt = '';
				foreach ( $result['highlighting'][ $doc['id'] ] as $name => $section ) {
					foreach ( $section as $item ) {
						if ( strlen( $current_excerpt ) < strlen( trim( $item ) ) ) {
							$current_excerpt = trim( $item );
						}
					}
				}
				$temp['excerpt'] = $current_excerpt;

				// get the thumb
				$thumb = '/wp-content/themes/orbit-media-bootstrap4/resources/images/fpo-blog-1.jpg';
				if ( ! empty( $doc["og_image"] ) ) {
					foreach ( $doc["og_image"] as $link ) {
						if ( substr( $link, - 3 ) == 'png' || substr( $link, - 3 ) == 'jpg' || substr( $link, - 3 ) == 'gif' ) {
							$thumb = $link;
						}
					}
				}
				$temp['thumbnail'] = $thumb;

				$lc['results'][] = $temp;
			}
		}
		if ( $lc['count'] > 0 ) {
			$retval['length'] ++;
		}
		$retval['blog'] = $lc;

		// answers

		$lc = array( 'count' => 0, 'results' => array() );
		/*
		if($a_count > 0) $retval['length']++;
		for($i = 0; $i < $a_count; $i++) {
		  $temp = array(
			  'url' => 'https://google.com',
			  'question' => 'Sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus?',
			  'answer' => 'Sociis natoque penatibus et <span class="highlighted">query</span> magnis dis parturient montes, nascetur ridiculus mus fusce dapibus, tellus ac cursus….',
			  'first_name' => 'John',
			  'last_name' => 'Smith',
			  'person_title' => 'Director of Search Relevance, Salesforce',
			  'profile_pic' => 'http://lucidworks.restlessdev.com/images/fpo-avatar-small.png'
		  );
		  $lc['results'][] = $temp;
		}
		*/
		$retval['answers'] = $lc;

		// video
		$lc = array( 'count' => 0, 'results' => array() );
		// $video_result = json_decode(file_get_contents($search_server.'/api/apollo/query-pipelines/site-search-videos/collections/lucidfind/select?wt=json&rows=100&q='.urlencode($query).'%20id:*video*'), true);
		$lc['count'] = ! empty( $groups['videos']['numFound'] ) ? $groups['videos']['numFound'] : 0;
		if ( ! empty( $groups['videos']['docs'] ) ) {
			foreach ( $groups['videos']['docs'] as $doc ) {
				if ( substr( $doc['id'], 0, strlen( "https://www.youtube.com/watch?v=" ) ) == "https://www.youtube.com/watch?v=" ) {
					$temp            = array(
						'url'       => $doc['id'],
						'caption'   => $doc['title'],
						'thumbnail' => 'https://img.youtube.com/vi/' . substr( $doc['id'], strpos( $doc['id'], '=' ) + 1 ) . '/hqdefault.jpg'
					);
					$lc['results'][] = $temp;
				}

			}
		}
		if ( $lc['count'] > 0 ) {
			$retval['length'] ++;
		}
		$retval['video'] = $lc;

		// search hub
		// $url = $search_server.'/api/apollo/query-pipelines/lucidfind-default/collections/lucidfind/select?wt=json&q='.urlencode($query);
		// echo $url;
		$lc = array( 'count' => 0, 'results' => array() );
		// $result = json_decode(file_get_contents($url), true);
		// var_dump($url, file_get_contents($url)); die;
		$lc['count'] = ! empty( $groups['hub']['numFound'] ) ? $groups['hub']['numFound'] : 0;
		if ( $lc['count'] > 0 ) {
			foreach ( $groups['hub']['docs'] as $doc ) {
				$temp = array(
					'url'         => ! empty( $doc['parent_s'] ) ? $doc['parent_s'] : $doc['id'],
					'description' => ! empty( $doc['content'] ) ? strip_tags( $doc['content'] ) : ( ! empty( $doc['body'] ) ? strip_tags( $doc['body'] ) : '' ),
					'title'       => $doc['title']
				);
				// get the excerpt
				$current_excerpt = '';
				foreach ( $result['highlighting'][ $doc['id'] ] as $name => $section ) {
					foreach ( $section as $item ) {
						if ( strlen( $current_excerpt ) < strlen( trim( $item ) ) ) {
							$current_excerpt = trim( $item );
						}
					}
				}
				if ( trim( $current_excerpt ) != '' ) {
					$temp['description'] = strip_tags( $current_excerpt, '<em>' );
				}
				if ( ! empty( $doc['project'] ) ) {
					$temp['project'] = $doc['project'];
				}
				if ( ! empty( $doc['datasource_label'] ) ) {
					$temp['datasource'] = $doc['datasource_label'];
				}
				$lc['results'][] = $temp;
			}
		}
		if ( $lc['count'] > 0 ) {
			$retval['length'] ++;
		}
		$retval['hub'] = $lc;

	}

	echo json_encode( $retval );

} else if ( $_REQUEST['type'] == 'blog-search' ) {
	$lc          = array();
	$query       = $_REQUEST['q'];
	$offset      = $_REQUEST['offset'] ? $_REQUEST['offset'] : 0;
	// OLD API endpoint
	//$result      = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/site-search-blog/collections/lucidfind/select?wt=json&start=' . urlencode( $offset ) . '&q=' . urlencode( $query ) ), TRUE );
	// New API endpoint
	$result = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/site-search-blog/collections/sitesearch/select?wt=json&q=' . urlencode( $query ) ), TRUE );

	$lc['count'] = $result['response']['numFound'];
	foreach ( $result['response']['docs'] as $doc ) {
		$temp = array(
			'url'            => $doc['id'],
			'title'          => $doc['title'],
			'date'           => '',
			'date_formatted' => ''
		);
		// get the excerpt
		$current_excerpt = '';
		foreach ( $result['highlighting'][ $doc['id'] ] as $name => $section ) {
			foreach ( $section as $item ) {
				if ( strlen( $current_excerpt ) < strlen( trim( $item ) ) ) {
					$current_excerpt = trim( $item );
				}
			}
		}
		$temp['excerpt'] = $current_excerpt;

		// get the thumb
		$thumb = '/wp-content/themes/orbit-media-bootstrap4/resources/images/fpo-blog-1.jpg';
		if ( ! empty( $doc["og_image"] ) ) {
			foreach ( $doc["og_image"] as $link ) {
				if ( substr( $link, - 3 ) == 'png' || substr( $link, - 3 ) == 'jpg' || substr( $link, - 3 ) == 'gif' ) {
					$thumb = $link;
				}
			}
		}
		$temp['thumbnail'] = $thumb;

		$lc['results'][] = $temp;
	}
	echo json_encode( $lc );
} else if ( $_REQUEST['type'] == 'documentation-search' ) {
	$lc     = array( 'count' => $d_count, 'results' => array() );
	$query  = $_REQUEST['q'];
	$offset = ! empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
	$params = array();
	if ( ! empty( $_REQUEST['version'] ) ) {
		$params[] = 'productVersion:' . urlencode( $_REQUEST['version'] );
	}
	if ( ! empty( $_REQUEST['product'] ) ) {
		$params[] = 'productName:' . urlencode( $_REQUEST['product'] );
	}

	// Old API
	//$result = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/site-search-documentation/collections/lucidfind/select?start=' . urlencode( $offset ) . '&wt=json&q=' . urlencode( $query ) . ( count( $params ) > 0 ? '&fq=' . implode( '%20AND%20', $params ) : '' ) ), TRUE );
	// New API
	$result = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/site-search-documentation/collections/sitesearch/select?wt=json&q=' . urlencode( $query ) ), TRUE );


	/*
	$version = !empty($_REQUEST['version']) ? $_REQUEST['version'] : 3.0;
	echo $search_server.'/api/apollo/query-pipelines/site-search-documentation/collections/lucidfind/select?start='.urlencode($offset).'&wt=json&q=' . urlencode($query) . '&fq=productVersion:' . urlencode($version);
	$result = json_decode(file_get_contents($search_server.'/api/apollo/query-pipelines/site-search-documentation/collections/lucidfind/select?start='.urlencode($offset).'&wt=json&q=' . urlencode($query) . '&fq=productVersion:' . urlencode($version)), true);
	*/
	// var_dump($result);
	$lc['count'] = $result['response']['numFound'];
	foreach ( $result['response']['docs'] as $doc ) {
		$temp            = array(
			'url'     => $doc['id'],
			'title'   => $doc['title'],
			'product' => $doc['productName'],
			'version' => $doc['productVersion']
		);
		$current_excerpt = '';
		foreach ( $result['highlighting'][ $doc['id'] ] as $name => $section ) {
			foreach ( $section as $item ) {
				if ( strlen( $current_excerpt ) < strlen( trim( $item ) ) ) {
					$current_excerpt = trim( $item );
				}
			}
		}
		$temp['excerpt'] = $current_excerpt;
		$lc['results'][] = $temp;
	}
	if ( $callback_wrapper ) {
		echo $callback_wrapper . '(';
	}
	echo json_encode( $lc );
	if ( $callback_wrapper ) {
		echo ');';
	}
} else if ( $_REQUEST['type'] == 'video-search' ) {
	$lc     = array();
	$query  = $_REQUEST['q'];
	$offset = $_REQUEST['offset'] ? $_REQUEST['offset'] : 0;

	// Old API
	//$result = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/site-search-videos/collections/lucidfind/select?wt=json&start=' . urlencode( $offset ) . '&rows=100&q=' . urlencode( $query ) . '' ), TRUE );
	// New API
	$result = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/site-search-videos/collections/sitesearch/select?q=' . urlencode( $query ). '&wt=json' ), TRUE );


	$lc['count'] = $result['response']['numFound'];
	foreach ( $result['response']['docs'] as $doc ) {
		if ( substr( $doc['id'], 0, strlen( "https://www.youtube.com/watch?v=" ) ) == "https://www.youtube.com/watch?v=" ) {
			$temp            = array(
				'url'       => $doc['id'],
				'caption'   => $doc['title'],
				'thumbnail' => 'https://img.youtube.com/vi/' . substr( $doc['id'], strpos( $doc['id'], '=' ) + 1 ) . '/hqdefault.jpg'
			);
			$lc['results'][] = $temp;
		}
	}
	echo json_encode( $lc );
} else if ( $_REQUEST['type'] == 'hub-search' ) {
	$lc       = array();
	$query    = ! empty( $_REQUEST['q'] ) ? $_REQUEST['q'] : '*';
	$offset   = ! empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
	$origSort = ! empty( $_REQUEST['sort'] ) ? $_REQUEST['sort'] : FALSE;
	$dir      = ! empty( $_REQUEST['dir'] ) && $_REQUEST['dir'] == 'asc' ? 'asc' : 'desc';
	if ( ( $query == '*' || $query == '*:*' ) && ( ! $origSort || $origSort == 'relevancy' ) ) {
		$origSort = 'published';
	}
	switch ( $origSort ) {
		case 'author':
			$sort = 'author';
			break;
		case 'published':
			$sort = 'publishedOnDate';
			break;
		default:
			$sort     = 'score';
			$origSort = 'relevancy';

	}
	$facets = '';

	$tag_lookup = array(
		'datasource_label' => 'ds',
		'project_label'    => 'prj',
		'author_facet'     => 'auth'
	);

	$query_facets = array();
	if ( ! empty( $_REQUEST['facets'] ) ) {
		foreach ( $_REQUEST['facets'] as $key => $val ) {
			if ( ! isset( $query_facets[ $key ] ) ) {
				$query_facets[ $key ] = array();
			}
			$val_parts   = explode( '|', $val );
			$facet_group = array();
			foreach ( $val_parts as $part ) {
				$query_facets[ $key ][] = $part;
				$facet_group[]          = '"' . urlencode( $part ) . '"';
				// $facets .= '&fq={!tag='.$tag_lookup[$key].'}' . urlencode($key) . ':("' .urlencode($part).'")';
				// $facets .= '&fq=' . urlencode($key) . ':("' .urlencode($part).'")';
			}
			$facets .= '&fq={!tag=' . $tag_lookup[ $key ] . '}' . urlencode( $key ) . ':(' . implode( ',', $facet_group ) . ')';

		}
	}
	$offset_string = '';
	$offset_obj    = array(
		'datasource_label' => 0,
		'project_label'    => 0,
		'author_facet'     => 0
	);
	if ( ! empty( $_REQUEST['facet_offsets'] ) ) {
		foreach ( $_REQUEST['facet_offsets'] as $key => $val ) {
			$offset_string      .= '&f.' . urlencode( $key ) . '.facet.offset=' . urlencode( $val );
			$offset_obj[ $key ] = $val;
		}
	}
	$fields_str = '&fl=author,project_label,datasource_label,title,parent_s,id,publishedOnDate';
	if ( $query == '*' ) {
		$fields_str .= ',body';
	}
	$url = $search_server . '/api/apollo/query-pipelines/lucidfind-default/collections/lucidfind/select?wt=json' . $fields_str . $offset_string . '&start=' . urlencode( $offset ) . '&q=' . urlencode( $query ) . ( $facets ? $facets : '' ) . '&sort=' . urlencode( $sort . ' ' . $dir );
	// echo $url; die;
	$result = json_decode( file_get_contents( $url ), TRUE );
	// echo file_get_contents($url); die;
	// $lc['query'] = $url;
	$lc['offsets'] = $offset_obj;
	$lc['count']   = $result['response']['numFound'];
	foreach ( $result['response']['docs'] as $doc ) {
		$temp = array(
			'url'         => ! empty( $doc['parent_s'] ) ? $doc['parent_s'] : $doc['id'],
			'description' => ! empty( $doc['content'] ) ? strip_tags( $doc['content'] ) : ( ! empty( $doc['body'] ) ? strip_tags( $doc['body'] ) : '' ),
			'title'       => ! empty( $doc['title'] ) ? $doc['title'] : '',
			'author'      => ! empty( $doc['author'] ) ? $doc['author'] : '',
			'pubdate'     => ! empty( $doc['publishedOnDate'] ) ? $doc['publishedOnDate'] : ''
		);
		// get the excerpt
		$current_excerpt = '';
		foreach ( $result['highlighting'][ $doc['id'] ] as $name => $section ) {
			// var_dump($name, $section);
			foreach ( $section as $item ) {
				if ( strlen( $current_excerpt ) < strlen( trim( $item ) ) ) {
					$current_excerpt = trim( $item );
				}
			}
		}
		if ( trim( $current_excerpt ) != '' ) {
			$temp['description'] = $current_excerpt;
		}
		if ( $doc['project_label'] ) {
			$temp['project'] = $doc['project_label'];
		}
		if ( $doc['datasource_label'] ) {
			$temp['datasource'] = $doc['datasource_label'];
		}
		$lc['results'][] = $temp;
	}
	// add the facets
	$lc['facets'] = array();
	foreach ( $result['facet_counts']['facet_fields'] as $key => $val ) {
		$lc['facets'][ $key ] = array();
		for ( $i = 0; $i < count( $val ); $i = $i + 2 ) {
			$temp = array(
				'value' => $val[ $i ],
				'count' => $val[ $i + 1 ]
			);
			// var_dump($query_facets,$key, $val[$i]);
			if ( ! empty( $query_facets[ $key ] ) && in_array( $val[ $i ], $query_facets[ $key ] ) ) {
				$temp['selected'] = TRUE;
			} else {
				$temp['selected'] = FALSE;
			}
			$lc['facets'][ $key ][] = $temp;
		}
	}
	// add selected facets
	$lc['selected_facets'] = $query_facets;
	$lc['sort']            = $origSort;
	echo json_encode( $lc );
} else if ( $_REQUEST['type'] == 'facet-page' ) {
	$lc     = array();
	$query  = ! empty( $_REQUEST['q'] ) ? $_REQUEST['q'] : '*';
	$offset = ! empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
	$facet  = ! empty( $_REQUEST['facet'] ) ? $_REQUEST['facet'] : 0;
	$sort   = ! empty( $_REQUEST['sort'] ) ? $_REQUEST['sort'] : FALSE;
	$dir    = ! empty( $_REQUEST['dir'] ) && $_REQUEST['dir'] == 'asc' ? 'asc' : 'desc';

	switch ( $sort ) {
		case 'author':
			$sort = 'author';
			break;
		case 'published':
			$sort = 'publishedOnDate';
			break;
		default:
			$sort = 'score';

	}
	$facets = '';

	$tag_lookup = array(
		'datasource_label' => 'ds',
		'project_label'    => 'prj',
		'author_facet'     => 'auth'
	);

	$query_facets = array();
	if ( ! empty( $_REQUEST['facets'] ) ) {
		foreach ( $_REQUEST['facets'] as $key => $val ) {
			if ( ! isset( $query_facets[ $key ] ) ) {
				$query_facets[ $key ] = array();
			}
			$val_parts   = explode( '|', $val );
			$facet_group = array();
			foreach ( $val_parts as $part ) {
				$query_facets[ $key ][] = $part;
				$facet_group[]          = '"' . urlencode( $part ) . '"';
				// $facets .= '&fq={!tag='.$tag_lookup[$key].'}' . urlencode($key) . ':("' .urlencode($part).'")';
				// $facets .= '&fq=' . urlencode($key) . ':("' .urlencode($part).'")';
			}
			$facets .= '&fq={!tag=' . $tag_lookup[ $key ] . '}' . urlencode( $key ) . ':(' . implode( ',', $facet_group ) . ')';

		}
	}
	$url = $search_server . '/api/apollo/query-pipelines/lucidfind-default/collections/lucidfind/select?wt=json&f.' . urlencode( $facet ) . '.facet.offset=' . urlencode( $offset ) . '&q=' . urlencode( $query ) . ( $facets ? $facets : '' ) . '&sort=' . urlencode( $sort . ' ' . $dir );
	// echo $url;
	$result = json_decode( file_get_contents( $url ), TRUE );
	// echo file_get_contents($url); die;
	$lc = array();
	for ( $i = 0; $i < count( $result['facet_counts']['facet_fields'][ $facet ] ); $i = $i + 2 ) {
		$temp = array(
			'value' => $result['facet_counts']['facet_fields'][ $facet ][ $i ],
			'count' => $result['facet_counts']['facet_fields'][ $facet ][ $i + 1 ]
		);
		if ( ! empty( $key ) && ! empty( $query_facets[ $key ] ) && in_array( $result['facet_counts']['facet_fields'][ $facet ][ $i ], $query_facets[ $key ] ) ) {
			$temp['selected'] = TRUE;
		} else {
			$temp['selected'] = FALSE;
		}
		$lc[] = $temp;
	}
	echo json_encode( $lc );
} else if ( $_REQUEST['type'] == 'training-search' ) {
	global $wpdb;
	$date_addition = " AND sdpm.meta_value >= '" . date( 'Ymd' ) . "' ";
	if ( ! empty( $_REQUEST['yearmonth'] ) && is_numeric( $_REQUEST['yearmonth'] ) ) {
		$time          = strtotime( $_REQUEST['yearmonth'] . '01' );
		$date_addition = " AND ( sdpm.meta_value LIKE '" . date( 'Ym', $time ) . "%' OR edpm.meta_value LIKE '" . date( 'Ym', $time ) . "%' ) ";
	}
	$city_addition = "";
	if ( ! empty( $_REQUEST['city'] ) ) {
		$city_addition = " AND cpm.meta_value = '" . addslashes( $_REQUEST['city'] ) . "' ";
	}
	$course_addition = "";
	if ( ! empty( $_REQUEST['course'] ) ) {
		$course_addition = " AND p.post_name = '" . addslashes( $_REQUEST['course'] ) . "' ";
	}

	$sql = "
      SELECT p.ID, p.post_title, p.post_excerpt,
        p.post_name, cpm.meta_value as city, lpm.meta_value as registration_link, 
        concat(substring(sdpm.meta_value, 1, 4), '-', substring(sdpm.meta_value, 5, 2), '-', substring(sdpm.meta_value, 7, 2)) as start_date,
        concat(substring(edpm.meta_value, 1, 4), '-', substring(edpm.meta_value, 5, 2), '-', substring(edpm.meta_value, 7, 2)) as end_date 
        FROM wp_posts p 
          INNER JOIN wp_postmeta cpm ON cpm.post_id = p.ID AND cpm.meta_key LIKE 'sessions_%_session_location'
		  INNER JOIN wp_postmeta lpm ON lpm.post_id = p.ID AND lpm.meta_key LIKE 'sessions_%_link_to_registration_site' AND SUBSTR(lpm.meta_key FROM 1 FOR LOCATE('_l', lpm.meta_key)) = SUBSTR(cpm.meta_key FROM 1 FOR LOCATE('_s', cpm.meta_key)) 
		  INNER JOIN wp_postmeta sdpm ON sdpm.post_id = p.ID AND sdpm.meta_key LIKE 'sessions_%_session_start_date' AND SUBSTR(sdpm.meta_key FROM 1 FOR LOCATE('_s', sdpm.meta_key)) = SUBSTR(cpm.meta_key FROM 1 FOR LOCATE('_s',cpm.meta_key))
		  INNER JOIN wp_postmeta edpm ON edpm.post_id = p.ID AND edpm.meta_key LIKE 'sessions_%_session_end_date' AND SUBSTR(edpm.meta_key FROM 1 FOR LOCATE('_s', edpm.meta_key)) = SUBSTR(cpm.meta_key FROM 1 FOR LOCATE('_s', cpm.meta_key)) 
        WHERE
          p.post_type = 'course' AND p.post_status = 'publish'
         " . $date_addition . $city_addition . $course_addition . " ORDER BY sdpm.meta_value ASC";
	// echo $sql;
	$result  = $wpdb->get_results( $sql );
	$courses = array();
	foreach ( $result as $row ) {
		$courses[] = array(
			'detail_url'       => get_the_permalink( $row->ID ),
			'registration_url' => $row->registration_link,
			'title'            => $row->post_title,
			'city'             => $row->city,
			'excerpt'          => $row->post_excerpt,
			'start_date'       => $row->start_date,
			'end_date'         => $row->end_date
		);
	}
	echo json_encode( $courses );
} else if ( $_REQUEST['type'] == 'typeahead' ) {
	$query   = $_REQUEST['q'];
	$page    = $_REQUEST['page'];
	$grouped = ! empty( $_REQUEST['grouped'] ) ? TRUE : FALSE;
	/*
	switch($page) {
	  case 'blog':
		$page = '&suggest.cfq=blog';
		break;
	  case 'documentation':
		$page = '&suggest.cfq=documentation';
		break;
	  case 'video':
		$page = '&suggest.cfq=video';
		break;
	  default:
		$page = '';

	}
	$result = json_decode(file_get_contents($search_server.'/api/apollo/query-pipelines/shub-typeahead/collections/lucidfind/suggest?q='.urlencode($query).$page), true);
	$retval = array();
	foreach($result['suggest']['blended_infix'] as $term => $suggest) {
	  foreach($suggest['suggestions'] as $item) {
		$retval[] = $item['term'];
	  }
	}
	*/
	switch ( $page ) {
		case 'blog':
			$page = '&fq=type:blog';
			break;
		case 'documentation':
			$page = '&fq=type:documentation';
			break;
		case 'video':
			$page = '&fq=type:video';
			break;
		case 'searchhub':
			$page = '&fq=NOT%20type:documentation%20AND%20NOT%20type:blog%20AND%20NOT%20type:video%20AND%20NOT%20type:support';
			break;
		default:
			$page = '';

	}

	$result = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/typeahead_test-grouped/collections/shub-_typeahead/select?' . ( $grouped ? 'group=true&fl=id%20title%20parent_s%20productVersion&' : '' ) . 'wt=json&q=' . urlencode( $query ) . $page ), TRUE );

	$retval = array();
	if ( ! $grouped ) {
		foreach ( $result['highlighting'] as $key => $item ) {
			foreach ( $item as $name => $val ) {
				if ( $name == 'name_edge' ) {
					$retval[] = $val[0];
				}
			}
		}
	} else {
		// $retval = $result['highlighting'];
		foreach ( $result['grouped']['type']['groups'] as $groups ) {
			$groupName = 'hub';
			if ( ! is_null( $groups['groupValue'] ) ) {
				$groupName = $groups['groupValue'];
			}
			$retval[ $groupName ]            = array();
			$retval[ $groupName ]['count']   = $groups['doclist']['numFound'];
			$retval[ $groupName ]['results'] = array();
			foreach ( $groups['doclist']['docs'] as $doc ) {
				if ($groupName === 'blog') {
					$temp = [
						'text' => !empty($result['highlighting'][$doc['id']]['name_edge'][0]) ? $result['highlighting'][$doc['id']]['name_edge'][0] : $doc['title'],
						'url' => $doc['id']
					];
				} else {
					$temp = [
						'text' => !empty($result['highlighting'][$doc['id']]['name_edge'][0]) ? $result['highlighting'][$doc['id']]['name_edge'][0] : $doc['title'],
						'url' => (empty($doc['parent_s']) ? $doc['id'] : $doc['parent_s'])
					];
				}
				// TODO: Replace this back!
				//if($doc['productVersion']) {
				if ( ! empty( $doc['productVersion'] ) ) {
					$temp['version'] = $doc['productVersion'];
				}
				$retval[ $groupName ]['results'][] = $temp;
			}
		}
	}
	// make sure every section has something
	$temp = array( 'hub', 'blog', 'video', 'documentation' );
	foreach ( $temp as $item ) {
		if ( empty( $retval[ $item ] ) ) {
			$retval[ $item ] = array( 'count' => 0, 'results' => array() );
		}
	}
	if ( $callback_wrapper ) {
		echo $callback_wrapper . '(';
	}
	echo json_encode( $retval );
	if ( $callback_wrapper ) {
		echo ');';
	}
} else if ( $_REQUEST['type'] == 'labs-search' ) {
	global $wpdb;

	$valid_categories = array( 'all', 'apps', 'connectors', 'index-pipelines' );
	$category         = ! empty( $_REQUEST['category'] ) && in_array( $_REQUEST['category'], $valid_categories ) ? $_REQUEST['category'] : 'all';
	$search           = ! empty( $_REQUEST['q'] ) ? $_REQUEST['q'] : FALSE;
	$version          = ! empty( $_REQUEST['version'] ) && is_numeric( $_REQUEST['version'] ) ? $_REQUEST['version'] : FALSE;
	$page             = ! empty( $_REQUEST['page'] ) && is_numeric( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1;
	$sql              = "
        SELECT SQL_CALC_FOUND_ROWS p.ID, p.post_title, p.post_excerpt, group_concat(DISTINCT t.name) as category, vf.meta_value as fusion_version_from, vt.meta_value as fusion_version_to
        	FROM wp_posts p
        		INNER JOIN wp_postmeta vf ON vf.post_id = p.ID AND vf.meta_key = 'fusion_from'
          	INNER JOIN wp_postmeta vt ON vt.post_id = p.ID AND vt.meta_key = 'fusion_to'
          	INNER JOIN wp_term_relationships tr ON tr.object_id = p.ID
            INNER JOIN wp_term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
            INNER JOIN wp_terms t ON t.term_id = tt.term_id
        	  LEFT OUTER JOIN wp_postmeta al ON al.post_id = p.ID AND al.meta_key LIKE 'authors_%_author_name'
        	WHERE p.post_type = 'labs-item' AND p.post_status = 'publish'
        	" . ( $search ? " AND ( p.post_title LIKE '%" . addslashes( $search ) . "%' OR p.post_content LIKE '%" . addslashes( $search ) . "%' OR p.post_excerpt LIKE '%" . addslashes( $search ) . "%' OR al.meta_value LIKE '%" . addslashes( $search ) . "%'  ) " : "" ) . "
        	" . ( $version ? " AND ( vf.meta_value <= '" . addslashes( $version ) . "' AND vt.meta_value >= '" . addslashes( $version ) . "') " : '' ) . "
        	" . ( $category && $category != 'all' ? " AND ( t.slug = '" . addslashes( $category ) . "') " : '' ) . "
          GROUP BY p.ID
          ORDER BY p.post_date DESC
          LIMIT " . ( ( $page - 1 ) * 6 ) . ", 6
      ";
	// echo $sql;
	$result       = $wpdb->get_results( $sql, ARRAY_A );
	$total_result = $wpdb->get_var( "SELECT found_rows();" );
	$retval       = array( 'count' => $total_result, 'results' => array() );
	foreach ( $result as $row ) {
		$row['url']                  = $permalink = get_permalink( $row['ID'] );
		$row['background_variation'] = $row['ID'] % 5;
		unset( $row['ID'] );
		// $retval[] = $row;
		$retval['results'][] = $row;
	}
	echo json_encode( $retval );
} else if ( $_REQUEST['type'] == 'snowplow-hit' ) {
	// curl -H "Content-Type: application/json" -d @test_snowplow.json -X
	// POST http://localhost:5000/snowplow_post/com.snowplowanalytics.snowplow
	// NOTE we dont want to do a GET here. We want to do a POST to this endpoint so lets do that.
	// The request will have the following information
	// type:snowplow-hit, data:[{"aid": "searchHub", url: url, query: query, rank: rank}]
	$request_body = file_get_contents( 'php://input' );
	$request_data = (array) json_decode( $request_body );

	// TODO: Make this configurable with the search_server param
	$url = $search_server . '/snowplow_post/com.snowplowanalytics.snowplow';

	$correct_ip                    = $_SERVER['REMOTE_ADDR'];
	$correct_agent                 = $_SERVER['HTTP_USER_AGENT'];
	$request_data['correct_ip']    = $correct_ip;
	$request_data['correct_agent'] = $correct_agent;
	// echo join(",", array_keys($request_data));
	// echo join(",", array_values($request_data));
	// echo "\n";

	$content_type            = "application/json";
	$_SERVER["Content-type"] = $content_type;
	echo json_encode( getallheaders() );
	echo( "\n" );
	$retval = array();
	foreach ( getallheaders() as $key => $value ) {
		if ( $key != "Content-Length" ) {
			$retval[] = "$key: $value";
		}
	}
	echo json_encode( $retval );
	echo( "\n" );
	echo json_encode( array( "Content-type: application/json" ) );
	echo( "\n" );
	# echo join(",", array_keys($_SERVER));
	# echo ("\n");
	# echo join(",", array_values($_SERVER));
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_HEADER, FALSE );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt( $curl, CURLOPT_HTTPHEADER, $retval );
	curl_setopt( $curl, CURLOPT_POST, TRUE );
	curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $request_data ) );
	$json_response = curl_exec( $curl );
	echo json_encode( $json_response );
	$status = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
} else if ( $_REQUEST['type'] == 'query_similarities' ) {
	$query    = $_REQUEST['q'];
	$result   = json_decode( file_get_contents( $search_server . '/api/apollo/query-pipelines/query-similarities/collections/query-similarities/select?&wt=json&q=' . urlencode( $query ) ), TRUE );
	$response = array();
	// var_dump($result['response']['docs']);
	if ( $result['response'] && $result['response']['docs'] && count( $result['response']['docs'] ) > 0 ) {
		foreach ( $result['response']['docs'] as $item ) {
			$response[] = $item["otherItemId"];
		}
	}
	echo json_encode( $response );
	// var_dump($result, $search_server.'/api/apollo/query-pipelines/query-similarities/collections/query-similarities/select?&wt=json&q='.urlencode($query));
	// TODO: Figure out what to do with the results!
}

// echo json_encode($response);
?>