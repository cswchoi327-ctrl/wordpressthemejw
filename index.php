<?php
/**
 * 지원금 스킨 - 메인 인덱스
 */
get_header();
?>

<div class="support-main-wrapper">
    <div class="support-container">
        <div class="intro-section">
            <span class="intro-badge">최신 정보</span>
            <p class="intro-sub">지원금 정보 모음</p>
            <h2 class="intro-title">지원금 찾기</h2>
        </div>

        <?php if (have_posts()): ?>
            <div class="info-card-grid">
                <?php while (have_posts()): the_post(); ?>
                    <div class="info-card">
                        <div class="info-card-content">
                            <h3 class="info-card-title"><?php the_title(); ?></h3>
                            <p class="info-card-desc"><?php the_excerpt(); ?></p>
                            <a href="<?php the_permalink(); ?>" class="info-card-btn">
                                자세히 보기 <span class="btn-arrow">→</span>
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="text-align: center; padding: 40px; color: #666;">
                게시물이 없습니다.<br>
                "지원금 카드" 메뉴에서 새 카드를 추가하세요.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
