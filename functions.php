<?php
/**
 * 지원금 스킨 Functions
 * functions.php에 추가할 코드
 */

// 커스텀 포스트 타입 등록
function register_support_card_post_type() {
    register_post_type('support_card', [
        'labels' => [
            'name' => '지원금 카드',
            'singular_name' => '지원금 카드',
            'add_new' => '새 카드 추가',
            'add_new_item' => '새 지원금 카드 추가',
            'edit_item' => '카드 편집',
            'view_item' => '카드 보기',
            'search_items' => '카드 검색',
            'not_found' => '카드가 없습니다',
        ],
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-money-alt',
        'supports' => ['title', 'excerpt', 'page-attributes'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'register_support_card_post_type');

// 메타 박스 추가
function add_support_card_meta_boxes() {
    add_meta_box(
        'support_card_details',
        '카드 상세 정보',
        'render_support_card_meta_box',
        'support_card',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_support_card_meta_boxes');

// 메타 박스 렌더링
function render_support_card_meta_box($post) {
    wp_nonce_field('support_card_meta', 'support_card_meta_nonce');
    
    $amount = get_post_meta($post->ID, '_card_amount', true);
    $amount_sub = get_post_meta($post->ID, '_card_amount_sub', true);
    $target = get_post_meta($post->ID, '_card_target', true);
    $period = get_post_meta($post->ID, '_card_period', true);
    $link = get_post_meta($post->ID, '_card_link', true);
    $featured = get_post_meta($post->ID, '_card_featured', true);
    
    ?>
    <style>
        .support-card-field { margin-bottom: 15px; }
        .support-card-field label { display: block; font-weight: bold; margin-bottom: 5px; }
        .support-card-field input[type="text"],
        .support-card-field input[type="url"] { width: 100%; padding: 8px; }
        .support-card-field input[type="checkbox"] { margin-right: 5px; }
    </style>
    
    <div class="support-card-field">
        <label>금액/혜택 강조 *</label>
        <input type="text" name="card_amount" value="<?php echo esc_attr($amount); ?>" 
               placeholder="예: 최대 4.5% 금리" required />
    </div>
    
    <div class="support-card-field">
        <label>부가 설명</label>
        <input type="text" name="card_amount_sub" value="<?php echo esc_attr($amount_sub); ?>" 
               placeholder="예: 비과세 + 대출 우대" />
    </div>
    
    <div class="support-card-field">
        <label>지원대상 * (20글자 이내)</label>
        <input type="text" name="card_target" value="<?php echo esc_attr($target); ?>" 
               placeholder="예: 만 19~34세 청년" maxlength="20" required />
    </div>
    
    <div class="support-card-field">
        <label>신청시기 *</label>
        <input type="text" name="card_period" value="<?php echo esc_attr($period); ?>" 
               placeholder="예: 상시" required />
    </div>
    
    <div class="support-card-field">
        <label>링크 URL</label>
        <input type="url" name="card_link" value="<?php echo esc_attr($link); ?>" 
               placeholder="https://example.com" />
    </div>
    
    <div class="support-card-field">
        <label>
            <input type="checkbox" name="card_featured" value="1" <?php checked($featured, '1'); ?> />
            인기 카드로 표시
        </label>
    </div>
    
    <p><strong>참고:</strong> 카드 제목은 위의 제목 필드에, 한 줄 설명은 발췌 필드에 입력하세요.</p>
    <?php
}

// 메타 데이터 저장
function save_support_card_meta($post_id) {
    if (!isset($_POST['support_card_meta_nonce']) || 
        !wp_verify_nonce($_POST['support_card_meta_nonce'], 'support_card_meta')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    $fields = ['card_amount', 'card_amount_sub', 'card_target', 'card_period', 'card_link'];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
    
    // 체크박스
    $featured = isset($_POST['card_featured']) ? '1' : '0';
    update_post_meta($post_id, '_card_featured', $featured);
}
add_action('save_post_support_card', 'save_support_card_meta');

// 설정 페이지 추가
function add_support_settings_page() {
    add_options_page(
        '지원금 스킨 설정',
        '지원금 스킨',
        'manage_options',
        'support-skin-settings',
        'render_support_settings_page'
    );
}
add_action('admin_menu', 'add_support_settings_page');

// 설정 페이지 렌더링
function render_support_settings_page() {
    if (isset($_POST['support_settings_submit'])) {
        check_admin_referer('support_settings');
        
        update_option('support_main_url', sanitize_text_field($_POST['support_main_url']));
        update_option('support_ad_platform', sanitize_text_field($_POST['support_ad_platform']));
        update_option('support_ad_code', wp_kses_post($_POST['support_ad_code']));
        
        // 탭 데이터 저장
        $tabs = [];
        for ($i = 0; $i < 3; $i++) {
            if (!empty($_POST["tab_name_$i"])) {
                $tabs[] = [
                    'name' => sanitize_text_field($_POST["tab_name_$i"]),
                    'link' => esc_url_raw($_POST["tab_link_$i"]),
                    'active' => isset($_POST["tab_active"]) && $_POST["tab_active"] == $i
                ];
            }
        }
        update_option('support_tabs', $tabs);
        
        echo '<div class="notice notice-success"><p>설정이 저장되었습니다.</p></div>';
    }
    
    $main_url = get_option('support_main_url', '');
    $ad_platform = get_option('support_ad_platform', 'adsense');
    $ad_code = get_option('support_ad_code', '');
    $tabs = get_option('support_tabs', []);
    
    ?>
    <div class="wrap">
        <h1>지원금 스킨 설정</h1>
        
        <form method="post">
            <?php wp_nonce_field('support_settings'); ?>
            
            <table class="form-table">
                <tr>
                    <th>메인 URL</th>
                    <td>
                        <input type="url" name="support_main_url" value="<?php echo esc_attr($main_url); ?>" 
                               class="regular-text" placeholder="https://example.com" />
                        <p class="description">카드 클릭 시 연결될 기본 URL</p>
                    </td>
                </tr>
                
                <tr>
                    <th>광고 플랫폼</th>
                    <td>
                        <select name="support_ad_platform">
                            <option value="adsense" <?php selected($ad_platform, 'adsense'); ?>>구글 애드센스</option>
                            <option value="dable" <?php selected($ad_platform, 'dable'); ?>>데이블</option>
                            <option value="taboola" <?php selected($ad_platform, 'taboola'); ?>>타뷸라</option>
                            <option value="custom" <?php selected($ad_platform, 'custom'); ?>>기타</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th>광고 코드</th>
                    <td>
                        <textarea name="support_ad_code" rows="8" class="large-text code"><?php echo esc_textarea($ad_code); ?></textarea>
                        <p class="description">광고 플랫폼에서 제공하는 코드를 붙여넣으세요</p>
                    </td>
                </tr>
                
                <tr>
                    <th>탭 설정</th>
                    <td>
                        <?php for ($i = 0; $i < 3; $i++): 
                            $tab = isset($tabs[$i]) ? $tabs[$i] : ['name' => '', 'link' => '', 'active' => false];
                        ?>
                        <div style="margin-bottom: 10px;">
                            <input type="text" name="tab_name_<?php echo $i; ?>" 
                                   value="<?php echo esc_attr($tab['name']); ?>" 
                                   placeholder="탭 이름" style="width: 200px;" />
                            <input type="url" name="tab_link_<?php echo $i; ?>" 
                                   value="<?php echo esc_attr($tab['link']); ?>" 
                                   placeholder="링크 URL" style="width: 300px;" />
                            <label>
                                <input type="radio" name="tab_active" value="<?php echo $i; ?>" 
                                       <?php checked($tab['active'], true); ?> />
                                활성
                            </label>
                        </div>
                        <?php endfor; ?>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="support_settings_submit" class="button button-primary" value="설정 저장" />
            </p>
        </form>
    </div>
    <?php
}

// 스타일시트와 스크립트 로드
function enqueue_support_skin_assets() {
    if (is_page_template('page-support.php')) {
        wp_enqueue_style('support-skin-style', get_template_directory_uri() . '/css/support-skin.css');
        wp_enqueue_script('support-skin-script', get_template_directory_uri() . '/js/support-skin.js', [], false, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_support_skin_assets');
