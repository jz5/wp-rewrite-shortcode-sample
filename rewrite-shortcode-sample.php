<?php
/*
Plugin Name: Rwrite shortcode sample
Author: jz5
License: GPLv2 or later
*/
/*
Copyright 2014 jz5

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// shortcode 処理
// [foo bar="qux" baz="quux"]
add_shortcode('foo', 'rewrite_shortcode_func');
function rewrite_shortcode_func($atts) {

	// shortcode のパラメータ取得
    extract(shortcode_atts(array(
        'bar' => 'qux', // bar 既定値
		'baz' => null // baz 既定値
    ), $atts));

    return "(bar = $bar, baz = $baz)"; // shortcode の結果
}

// 保存時 shortcode の書き換え
add_filter('wp_insert_post_data', 'rewrite_shortcode_insert_post_data');
function rewrite_shortcode_insert_post_data($data) {
	
	// foo shortcode がなければ書き換えなし
	if (false === has_shortcode($data['post_content'], 'foo')) {
		return $data;
	}
	
	// foo shortcode 部分の書き換え
	$pattern = get_shortcode_regex();
	
	// shortcode 部分を繰り返し取得して置換
	$data['post_content'] = preg_replace_callback('/'. $pattern .'/s', function ($matches) {
		
		// foo 以外の shortcode は書き換えなし
		if ($matches[2] !== 'foo') {
			return $matches[0];
		}
		
		// エスケープされた shortcode は書き換えなし
		if ($matches[1] == '[' && $matches[6] == ']') {
			return $matches[0];
		}

		// shortcode のパラメータ取得
		$atts = shortcode_parse_atts(stripslashes($matches[3]));

		// baz !== null （規定値以外）の場合も書き換えなし
		if ($atts['baz'] !== null) {
			return $matches[0];
		}
		
		// baz が既定値以外の場合 shortcode を書き換え
		$atts['baz'] = uniqid();
		
		$params = array();
		foreach ($atts as $key => $val) {
			$params[] = "$key=\"$val\"";
		}

		return '[foo ' . implode(' ', $params) . ']'; // 書き換えた shortcode
		
	}, $data['post_content']);

	return $data;
}

?>
