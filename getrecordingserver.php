<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Get recording server and share to Wespher so recording will be saved there.
 *
 * @package    mod_wespher
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/moodlelib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once($CFG->libdir . '/filelib.php');

/**
 * Encrypt data
 *
 * @param string $textoencrypt The textoencrypt
 * @return string Return Encrypted msg
 */
function encrption_data($textoencrypt) {

    $encryptionmethod = "AES-256-CBC";
    $secrethash = "25c6c7ff35b9979b151f2136cd13b0ff";
    return openssl_encrypt($textoencrypt, $encryptionmethod, $secrethash);
}

$leeloolxplicense = get_config('mod_wespher')->license;
$url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
$postdata = '&license_key=' . $leeloolxplicense;

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => 1,
);

if (!$output = $curl->post($url, $postdata, $options)) {
    notice(get_string('nolicense', 'mod_wespher'));
}

$infoleeloolxp = json_decode($output);

if ($infoleeloolxp->status != 'false') {
    $leeloolxpurl = $infoleeloolxp->data->install_url;
} else {
    notice(get_string('nolicense', 'mod_wespher'));
}

$url = $leeloolxpurl . '/admin/Theme_setup/get_wespher_conference_settings';

$postdata = '&license_key=' . $leeloolxplicense;

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => 1,
);

if (!$output = $curl->post($url, $postdata, $options)) {
    notice(get_string('nolicense', 'mod_wespher'));
}

$resposedata = json_decode($output);
$settingleeloolxp = $resposedata->data->wespher_conference;

$wespherdomain = $settingleeloolxp->wespher_domain;
$ftpftpsftp = $settingleeloolxp->ftp_sftp;
$ftpserver = $settingleeloolxp->recording_ftp_server;
$ftpserverport = $settingleeloolxp->recording_ftp_server_port;
$ftpusername = $settingleeloolxp->recording_ftp_user;
$ftpuserpass = $settingleeloolxp->recording_ftp_password;
$recordingftppath = $settingleeloolxp->recording_ftp_path;
$recordingbaseurl = $settingleeloolxp->recording_base_url;

$result = array();

$result['ftp_server'] = encrption_data($ftpserver);
$result['ftp_sftp'] = encrption_data($ftpftpsftp);
$result['ftp_server_port'] = encrption_data($ftpserverport);
$result['ftp_username'] = encrption_data($ftpusername);
$result['ftp_userpass'] = encrption_data($ftpuserpass);
$result['recording_ftp_path'] = encrption_data($recordingftppath);
$result['recording_base_url'] = encrption_data($recordingbaseurl);

echo json_encode($result);