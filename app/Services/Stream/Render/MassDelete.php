<?php
namespace Coyote\Services\Stream\Render;

class MassDelete extends Render {
    public function user(): string {
        return array_get($this->stream, 'object.displayName');
    }

    protected function excerpt() {
        return '';
    }

    public function contentType(): string {
        return match ($this->objectContentType()) {
            'deletePosts'        => 'posty',
            'deletePostComments' => 'komentarze do postów',
            'deleteBlogs'        => 'mikroblogi',
            'deleteBlogComments' => 'komentarze pod mikroblogami',
            'deletePostVotes'    => 'głosy oddane na posty',
            'deleteBlogVotes'    => 'głosy oddane na mikroblogi',
            'deleteFlags'        => 'raporty od',
            'deleteMessages'     => 'wiadomości prywatne',
            'deleteJobOffers'    => 'oferty pracy',
        };
    }

    private function objectContentType(): string {
        return array_get($this->stream, 'object.contentType');
    }
}
