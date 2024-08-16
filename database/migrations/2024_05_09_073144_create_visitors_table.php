<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('page');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('device');
            $table->string('ip_address');
            $table->date('visit_date');
            $table->text('accept')->nullable();
            $table->text('accept_charset')->nullable();
            $table->text('accept_encoding')->nullable();
            $table->text('accept_language')->nullable();
            $table->text('accept_datetime')->nullable();
            $table->text('access_control_request_method')->nullable();
            $table->text('access_control_request_headers')->nullable();
            $table->text('authorization')->nullable();
            $table->text('cache_control')->nullable();
            $table->text('connection')->nullable();
            $table->text('cookie')->nullable();
            $table->text('content_length')->nullable();
            $table->text('content_md5')->nullable();
            $table->text('content_type')->nullable();
            $table->text('expect')->nullable();
            $table->text('forwarded')->nullable();
            $table->text('from')->nullable();
            $table->text('host')->nullable();
            $table->text('if_match')->nullable();
            $table->text('if_modified_since')->nullable();
            $table->text('if_none_match')->nullable();
            $table->text('if_range')->nullable();
            $table->text('if_unmodified_since')->nullable();
            $table->text('max_forwards')->nullable();
            $table->text('origin')->nullable();
            $table->text('pragma')->nullable();
            $table->text('proxy_authorization')->nullable();
            $table->text('range')->nullable();
            $table->text('referer')->nullable();
            $table->text('te')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('upgrade')->nullable();
            $table->text('via')->nullable();
            $table->text('warning')->nullable();
            $table->text('x_requested_with')->nullable();
            $table->text('x_forwarded_for')->nullable();
            $table->text('front_end_https')->nullable();
            $table->text('x_http_method_override')->nullable();
            $table->text('x_att_deviceid')->nullable();
            $table->text('x_wap_profile')->nullable();
            $table->text('proxy_connection')->nullable();
            $table->text('x_uidh')->nullable();
            $table->text('x_csrf_token')->nullable();
            $table->text('x_request_id')->nullable();
            $table->text('x_correlation_id')->nullable();
            $table->text('x_flash_version')->nullable();
            $table->text('x_real_ip')->nullable();
            $table->text('x_requested_with_aka')->nullable();
            $table->text('x_operamini_phone_ua')->nullable();
            $table->text('x_operamini_phone')->nullable();
            $table->text('x_operamini_features')->nullable();
            $table->text('x_att_apollo_deviceid')->nullable();
            $table->text('x_att_deviceos')->nullable();
            $table->text('x_att_platform')->nullable();
            $table->text('x_att_appversion')->nullable();
            $table->text('x_att_mobile_version')->nullable();
            $table->text('x_saf_version')->nullable();
            $table->text('x_ucbrowser_deviceua')->nullable();
            $table->text('x_ucbrowser_device')->nullable();
            $table->text('x_ucbrowser_ua')->nullable();
            $table->text('x_ucbrowser_device_features')->nullable();
            $table->text('x_source_scheme')->nullable();
            $table->text('x_forwarded_proto')->nullable();
            $table->text('x_forwarded_port')->nullable();
            $table->text('x_forwarded_host')->nullable();
            $table->text('x_forwarded_server')->nullable();
            $table->text('x_proxy_user_ip')->nullable();
            $table->text('x_wap_clientid')->nullable();
            $table->text('x_att_proxy_user_ip')->nullable();
            $table->text('x_forwarded_scheme')->nullable();
            $table->text('x_att_auth_status')->nullable();
            $table->text('x_custom_header')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
