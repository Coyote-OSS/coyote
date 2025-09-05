<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration {
    use SchemaBuilder;

    public function up(): void {
        $this->db->statement('DROP MATERIALIZED VIEW topic_recent');
        $this->db->statement('CREATE MATERIALIZED VIEW topic_recent AS
         SELECT
            topics.id AS id,
            topics.forum_id,
            topics.title,
            topics.slug,
            topics.last_post_created_at,
            topics.views,
            topics.score,
            topics.replies,
            topics.deleted_at,
            topics.first_post_id,
            topics.rank,
            forums.name AS forum,
            forums.slug AS forum_slug
         FROM topics
         JOIN forums ON forums.id = forum_id
         JOIN posts ON posts.id = topics.first_post_id
         JOIN users ON users.id = posts.user_id 
         WHERE topics.is_locked = 0 
           AND forums.is_locked = 0 
           AND enable_homepage = true
           AND NOT users.is_incognito
         ORDER BY topics.id DESC LIMIT 3000');
        $this->schema->table('topic_recent', function (Blueprint $table) {
            $table->index('id');
            $table->index(['replies', $this->db->raw('rank DESC')],
                'topic_recent_replies_rank desc_index');
        });
    }

    public function down(): void {
        $this->db->statement('DROP MATERIALIZED VIEW topic_recent');
        $this->db->statement('CREATE MATERIALIZED VIEW topic_recent AS
         SELECT
            topics.id AS id,
            forum_id,
            topics.title,
            topics.slug,
            last_post_created_at,
            views,
            score,
            replies,
            deleted_at,
            first_post_id,
            rank,
            forums.name AS forum,
            forums.slug AS forum_slug
         FROM topics
         JOIN forums ON forums.id = forum_id
         WHERE topics.is_locked = 0 AND forums.is_locked = 0 AND enable_homepage = true
         ORDER BY topics.id DESC LIMIT 3000');
        $this->schema->table('topic_recent', function (Blueprint $table) {
            $table->index('id');
            $table->index(['replies', $this->db->raw('rank DESC')],
                'topic_recent_replies_rank desc_index');
        });
    }
};
