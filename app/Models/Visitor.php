<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use HasFactory;

    protected $fillable = [
        'page',
        'user_id',
        'device',
        'ip_address',
        'visit_date',
        'accept',
        'accept_charset',
        'accept_encoding',
        'accept_language',
        'accept_datetime',
        'access_control_request_method',
        'access_control_request_headers',
        'authorization',
        'cache_control',
        'connection',
        'cookie',
        'content_length',
        'content_md5',
        'content_type',
        'expect',
        'forwarded',
        'from',
        'host',
        'if_match',
        'if_modified_since',
        'if_none_match',
        'if_range',
        'if_unmodified_since',
        'max_forwards',
        'origin',
        'pragma',
        'proxy_authorization',
        'range',
        'referer',
        'te',
        'user_agent',
        'upgrade',
        'via',
        'warning',
        'x_requested_with',
        'x_forwarded_for',
        'x_forwarded_host',
        'x_forwarded_proto',
        'front_end_https',
        'x_http_method_override',
        'x_att_deviceid',
        'x_wap_profile',
        'proxy_connection',
        'x_uidh',
        'x_csrf_token',
        'x_request_id',
        'x_correlation_id',
        'x_flash_version',
        'x_real_ip',
        'x_requested_with_aka',
        'x_operamini_phone_ua',
        'x_operamini_phone',
        'x_operamini_features',
        'x_att_apollo_deviceid',
        'x_att_deviceos',
        'x_att_platform',
        'x_att_appversion',
        'x_att_mobile_version',
        'x_saf_version',
        'x_ucbrowser_deviceua',
        'x_ucbrowser_device',
        'x_ucbrowser_ua',
        'x_ucbrowser_device_features',
        'x_source_scheme',
        'x_forwarded_scheme',
        'x_forwarded_port',
        'x_forwarded_server',
        'x_proxy_user_ip',
        'x_wap_clientid',
        'x_att_proxy_user_ip',
        'x_forwarded_scheme',
        'x_att_auth_status',
        'x_custom_header',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
