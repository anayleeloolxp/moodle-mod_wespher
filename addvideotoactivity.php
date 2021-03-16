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
 * Add wespher recording as video to activity.
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
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

$leeloolxplicense = get_config('mod_wespher')->license;
$url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
$postdata = [
    'license_key' => $leeloolxplicense,
];

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => count($postdata),
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

$postdata = [
    'license_key' => $leeloolxplicense,
];

$curl = new curl;

$options = array(
    'CURLOPT_RETURNTRANSFER' => true,
    'CURLOPT_HEADER' => false,
    'CURLOPT_POST' => count($postdata),
);

if (!$output = $curl->post($url, $postdata, $options)) {
    notice(get_string('nolicense', 'mod_wespher'));
}

$resposedata = json_decode($output);
$settingleeloolxp = $resposedata->data->wespher_conference;

/**
 * Decrypt data
 *
 * @param string $encryptedmessage The encryptedmessage
 * @return string Return Decrypted msg
 */
function deencrption_data($encryptedmessage) {

    $encryptionmethod = "AES-256-CBC";
    $secrethash = "25c6c7ff35b9979b151f2136cd13b0ff";
    return openssl_decrypt($encryptedmessage, $encryptionmethod, $secrethash);
}

$wespherdomain = $settingleeloolxp->wespher_domain;
$ftpserver = $settingleeloolxp->recording_ftp_server;
$ftpusername = $settingleeloolxp->recording_ftp_user;
$ftpuserpass = $settingleeloolxp->recording_ftp_password;
$recordingftppath = $settingleeloolxp->recording_ftp_path;
$recordingbaseurl = $settingleeloolxp->recording_base_url;

$meetingname = deencrption_data(optional_param('meeting_name', 0, PARAM_RAW));
$recordingpath = deencrption_data(optional_param('recording_path', 0, PARAM_RAW));
$videoname = deencrption_data(optional_param('video_name', 0, PARAM_RAW));
$videourl = deencrption_data(optional_param('videourl', 0, PARAM_RAW));
$recordingurlbase = deencrption_data(optional_param('recording_url_base', 0, PARAM_RAW));

$checksql = 'SELECT * FROM {wespher} WHERE `roomname`= ?';
$wesphers = $DB->get_record_sql($checksql, [$meetingname]);

$result = array();

if ($wesphers) {
    if ($wesphers->recordedurl != "") {
        $result['old'] = $wesphers->recordedurl;
        $result['new'] = $recordingpath . '/' . $videoname;
        $recordingurlbase = str_ireplace('/recordings/', '', $recordingurlbase);
        $sql = 'UPDATE {wespher} SET recordedurl = ? WHERE roomname = ?';
        $DB->execute($sql, [$recordingurlbase.'/'.'output.mp4', $meetingname]);
    } else {

        $sql = 'UPDATE {wespher} SET recordedurl = ? WHERE roomname = ?';
        $DB->execute($sql, [$videourl, $meetingname]);
    }
}

echo json_encode($result);