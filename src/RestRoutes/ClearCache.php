<?php

namespace Rahmentemplate\RestRoutes;


use WP_REST_Request;
use WP_REST_Response;

class ClearCache
{
    public function register_routes(): void
    {
        add_action('rest_api_init', function () {
            register_rest_route('rahmentemplate/v1', '/clearCache', [
                'methods'  => 'POST',
                'callback' => [$this, 'handle_request'],
                'permission_callback' => '__return_true',
            ]);
        });
    }
    public function handle_request(WP_REST_Request $request): WP_REST_Response
    {
        $transient_keys = $request->get_param('transient_keys');

        if (is_array($transient_keys)) {
            foreach ($transient_keys as $transient_key) {
                delete_transient($transient_key);
            }

            return new WP_REST_Response(['success' => true], 200);
        }

        return new WP_REST_Response(['success' => false, 'message' => 'Invalid transient keys'], 400);
    }
}

