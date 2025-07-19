<?php
namespace Coyote\Domain\Icon;

readonly class FontAwesomePro {
    public function icons(): array {
        $faTick = 'fa-light fa-check';
        $faSolidTick = 'fa-solid fa-check';
        $faCross = 'fa-light fa-xmark';
        $faSpinner = 'fa-light fa-spinner';

        $genericUploading = $faSpinner;
        $genericLoading = $faSpinner;
        $genericClose = $faCross;
        $genericDropdown = 'fa-solid fa-ellipsis';
        $genericDropdownVertical = 'fa-solid fa-ellipsis-vertical';

        return [
            // global
            'scrollTop'                                 => 'fa-light fa-arrow-up',

            // alert
            'alertDanger'                               => 'fa-light fa-triangle-exclamation',
            'alertSuccess'                              => $faTick,

            // registration
            'loginGoogle'                               => 'fa-brands fa-google',
            'loginFacebook'                             => 'fa-brands fa-facebook',
            'loginGithub'                               => 'fa-brands fa-github',

            // footer
            'contactUsFacebook'                         => 'fa-brands fa-facebook-square',
            'contactUsLinkedIn'                         => 'fa-brands fa-linkedin',
            'contactUsGithub'                           => 'fa-brands fa-github-square',

            // homepage
            'viewersOnlineLocal'                        => 'fa-light fa-eye',
            'viewersOnlineGlobal'                       => 'fa-light fa-users',
            'homepageActivityMicroblog'                 => 'fa-light fa-person',
            'homepageActivityPostComment'               => 'fa-light fa-comments',
            'homepageActivityTopic'                     => 'fa-light fa-file',
            'homepageActivityPost'                      => 'fa-light fa-file-lines',

            // microblog page
            'recommendedUsers'                          => 'fa-light fa-user-plus',

            // microblog
            'microblogNew'                              => 'fa-light fa-plus',
            'microblogMine'                             => 'fa-light fa-address-book',
            'microblogReport'                           => 'fa-light fa-flag',
            'microblogShare'                            => 'fa-light fa-share-nodes',
            'microblogAddComment'                       => 'fa-light fa-comment',
            'microblogVote'                             => 'fa-light fa-thumbs-up',
            'microblogVoted'                            => 'fa-solid fa-thumbs-up',
            'microblogSubscribe'                        => 'fa-light fa-bell',
            'microblogSubscribed'                       => 'fa-solid fa-bell',
            'microblogFoldedUnfold'                     => 'fa-light fa-circle-ellipsis',
            'microblogCommentsFoldedUnfold'             => 'fa-light fa-comments',
            'microblogBlockAuthor'                      => 'fa-light fa-lock',
            'microblogEdit'                             => 'fa-light fa-edit',
            'microblogDelete'                           => 'fa-light fa-trash-can',
            'microblogRestore'                          => 'fa-light fa-trash-can-undo',
            'microblogSponsored'                        => 'fa-light fa-circle-dollar',
            'microblogMenuDropdown'                     => $genericDropdown,

            // microblog comment
            'microblogCommentEdit'                      => 'fa-light fa-edit',
            'microblogCommentDelete'                    => 'fa-light fa-trash-can',
            'microblogCommentRestore'                   => 'fa-light fa-trash-can-undo',
            'microblogCommentBlockAuthor'               => 'fa-light fa-lock',
            'microblogCommentSaveNew'                   => 'fa-light fa-share-from-square',
            'microblogCommentSaveExisting'              => 'fa-light fa-floppy-disk',
            'microblogCommentMenuEditRemove'            => 'fa-light fa-bars', // deprecated 
            'microblogCommentMenuAnswerFlag'            => $genericDropdown,

            // navigation dropdown
            'sponsorProfile'                            => 'fa-light fa-medal',
            'userProfile'                               => 'fa-light fa-user',
            'privateMessages'                           => 'fa-light fa-envelope',
            'userAccount'                               => 'fa-light fa-gear',
            'help'                                      => 'fa-light fa-square-question',
            'adminPanel'                                => 'fa-light fa-user-tie',
            'logout'                                    => 'fa-light fa-right-from-bracket',

            // navigation menu
            'mobileMenuOpen'                            => 'fa-light fa-bars',
            'mobileMenuClose'                           => $genericClose,
            'mobileSearchClose'                         => $genericClose,

            // private messages
            'privateMessageTyping'                      => 'fa-light fa-comment-dots',
            'privateMessagesLoading'                    => $genericLoading,
            'privateMessageDelete'                      => 'fa-light fa-trash-can',
            'privateMessageReadAt'                      => $faTick,
            'privateMessageMarkAsRead'                  => 'fa-light fa-eye',

            // user profile
            'userReputation'                            => 'fa-light fa-chart-line',
            'userLastVisitDate'                         => 'fa-light fa-right-to-bracket',
            'userRegisterDate'                          => 'fa-light fa-user-plus',
            'userIpAddress'                             => 'fa-light fa-house',
            'userVisits'                                => 'fa-light fa-eye',
            'userAboutMe'                               => 'fa-solid fa-quote-left',

            // user account settings
            'userAccount.userAccount'                   => 'fa-light fa-user',
            'userAccount.skills'                        => 'fa-light fa-address-book',
            'userAccount.privateMessageList'            => 'fa-light fa-envelope',
            'userAccount.notificationList'              => 'fa-light fa-bell',
            'userAccount.postVotes'                     => 'fa-light fa-thumbs-up',
            'userAccount.postCategories'                => 'fa-light fa-chart-bar',
            'userAccount.postAccepts'                   => $faTick,
            'userAccount.subscribedPages'               => 'fa-light fa-bell', // topics, jobOffers, microblogs
            'userAccount.relations'                     => 'fa-light fa-user-group',
            'userAccount.notificationSettings'          => 'fa-light fa-bell',
            'userAccount.miscellaneousSettings'         => 'fa-light fa-user-gear',
            'userAccount.passwordChange'                => 'fa-light fa-unlock-keyhole',
            'userAccount.access'                        => 'fa-light fa-door-open',
            'userAccount.apiTokens'                     => 'fa-light fa-key',
            'userAccount.accountDelete'                 => 'fa-light fa-trash-can',

            // following user
            'userFollow'                                => 'fa-light fa-bell',

            // report
            'reportClose'                               => $faCross,
            'reportType.spam'                           => 'fa-light fa-envelopes-bulk',
            'reportType.abusiveLanguage'                => 'fa-light fa-book-skull',
            'reportType.offTopic'                       => 'fa-light fa-wave-square',
            'reportType.category'                       => 'fa-light fa-table-list',
            'reportType.extortion'                      => 'fa-light fa-user-graduate',
            'reportType.other'                          => 'fa-light fa-flag',

            // category
            'forumCategory'                             => 'fa-light fa-comments',
            'forumCategoryLocked'                       => 'fa-light fa-lock',
            'categorySectionMenu'                       => 'fa-light fa-gears',
            'categorySectionMenuItemEnabled'            => $faTick,
            'categorySectionFolded'                     => 'fa-light fa-square-plus',
            'categorySectionFold'                       => 'fa-light fa-square-minus',
            'categorySectionMarkAsRead'                 => 'fa-light fa-eye',
            'categorySectionMoveUp'                     => 'fa-solid fa-caret-up',
            'categorySectionMoveDown'                   => 'fa-solid fa-caret-down',
            'categorySectionChildWasRead'               => 'fa-light fa-file',
            'categorySectionChildWasNotRead'            => null, // icon generated by css

            // poll
            'postPoll'                                  => 'fa-light fa-square-poll-horizontal',
            'postPollRemoveOption'                      => 'fa-light fa-circle-minus',

            // autocomplete
            'autocompleteUserShowProfile'               => 'fa-light fa-user',
            'autocompleteUserPrivateMessage'            => 'fa-light fa-comment',
            'autocompleteUserFindPosts'                 => 'fa-light fa-user-magnifying-glass',
            'autocompleteUserNoAvatar'                  => 'fa-light fa-user',
            'autocompleteSearch'                        => 'fa-light fa-magnifying-glass',

            // tags
            'tag'                                       => 'fa-light fa-tag',
            'tagRemove'                                 => $genericClose,
            'tagRank'                                   => 'fa-light fa-circle',
            'tagRanked'                                 => 'fa-solid fa-circle',
            'tagPopularInclude'                         => 'fa-light fa-plus',
            'tagPopularMore'                            => 'fa-light fa-plus',

            // asset thumbnail
            'thumbnailAssetRemove'                      => $genericClose,
            'thumbnailAssetAdd'                         => 'fa-light fa-circle-plus',
            'thumbnailAssetUploadedFile'                => 'fa-light fa-file',
            'thumbnailAssetUploading'                   => $genericUploading,

            // navigation bar
            'navigationPrivateMessages'                 => 'fa-light fa-envelope',
            'navigationSearch'                          => 'fa-light fa-magnifying-glass',
            'navigationNotifications'                   => 'fa-light fa-bell',

            // navigation notifications
            'notificationsMarkAllAsRead'                => 'fa-light fa-eye',
            'notificationsOpenInNewTab'                 => 'fa-light fa-up-right-from-square',
            'notificationsLoading'                      => $genericLoading,
            'notificationDelete'                        => $genericClose,

            // editor
            'editorMarkdownHelp'                        => 'fa-brands fa-markdown',
            'editorAssetUpload'                         => 'fa-light fa-image',
            'editorAssetUploading'                      => $genericUploading,
            'editorControlBold'                         => 'fa-light fa-bold',
            'editorControlItalics'                      => 'fa-light fa-italic',
            'editorControlUnderline'                    => 'fa-light fa-underline',
            'editorControlStrikeThrough'                => 'fa-light fa-strikethrough',
            'editorControlHyperlink'                    => 'fa-light fa-link',
            'editorControlCodeBlock'                    => 'fa-light fa-code',
            'editorControlImage'                        => 'fa-light fa-image',
            'editorControlKeyStroke'                    => 'fa-light fa-keyboard',
            'editorControlListOrdered'                  => 'fa-light fa-list-ol',
            'editorControlListUnordered'                => 'fa-light fa-list-ul',
            'editorControlQuote'                        => 'fa-light fa-quote-left',
            'editorControlTable'                        => 'fa-light fa-table',
            'editorControlIndentMore'                   => 'fa-light fa-indent',
            'editorControlIndentLess'                   => 'fa-light fa-outdent',
            'editorControlEmoji'                        => 'fa-light fa-face-smile-beam',
            'editorEmojiPickerClose'                    => $genericClose,
            'editorMarkdownHelpKeyArrowUp'              => 'fa-light fa-arrow-up',
            'editorMarkdownHelpKeyArrowDown'            => 'fa-light fa-arrow-down',
            'editorPasteLoading'                        => $genericUploading,

            // survey
            'surveyExperimentDueTime'                   => 'fa-light fa-clock',
            'surveyExperimentChoiceModern'              => 'fa-light fa-toggle-on',
            'surveyExperimentChoiceLegacy'              => 'fa-light fa-toggle-off',
            'surveyExperimentOpen'                      => 'fa-light fa-toggle-off',
            'surveyBadgeEnlarge'                        => 'fa-light fa-chevron-left',
            'surveyBadgeShorten'                        => 'fa-light fa-chevron-right',

            // payment
            'paymentSecureConnection'                   => 'fa-light fa-lock',
            'paymentNotNecessary'                       => $faTick,
            'paymentInvoiceData'                        => 'fa-light fa-lock',
            'paymentBackToOffer'                        => 'fa-light fa-angle-left',
            'paymentSaveAndPay'                         => 'fa-light fa-angle-right',
            'paymentCoupon'                             => 'fa-light fa-circle-dollar-to-slot',

            // error page
            'errorPageBackToHomepage'                   => 'fa-light fa-house',
            'errorPageContactUs'                        => 'fa-light fa-info',
            'errorPageNeedHelp'                         => 'fa-light fa-asterisk',

            // tags
            'tagsSubscribed'                            => 'fa-light fa-tags',
            'tagsSubscribedEdit'                        => 'fa-light fa-gear',
            'tagsPopularForum'                          => 'fa-light fa-tags',
            'tagsPopularMicroblog'                      => 'fa-light fa-tags',
            'tagsPopularLanguage'                       => 'fa-light fa-wrench',

            // forum
            'forumSidebarMobileMenu'                    => $genericDropdownVertical,
            'forumChangeCategory'                       => 'fa-light fa-circle-arrow-right',
            'forumActions'                              => 'fa-light fa-circle-dot',
            'forumGlobalMarkAsRead'                     => 'fa-light fa-eye',
            'forumCategoryMarkAsRead'                   => 'fa-light fa-eye',
            'forumTopicMarkAsRead'                      => 'fa-light fa-eye',

            // topic
            'topicSubscribe'                            => 'fa-light fa-bell',
            'topicSubscribed'                           => 'fa-solid fa-bell',
            'topicLog'                                  => 'fa-light fa-list-check',
            'topicGoToBeginning'                        => 'fa-light fa-backward-fast',
            'topicLogBackToTopic'                       => 'fa-light fa-backward-step',
            'topicActionRename'                         => 'fa-light fa-pen-line',
            'topicActionMove'                           => 'fa-light fa-circle-arrow-right',
            'topicActionLock'                           => 'fa-light fa-lock',
            'topicActionUnlock'                         => 'fa-light fa-unlock',
            'topicAccepted'                             => $faSolidTick,
            'topicReported'                             => 'fa-light fa-flag',
            'topicStateSticky'                          => 'fa-light fa-thumbtack',
            'topicStateLocked'                          => 'fa-light fa-lock',
            'topicStateStandard'                        => 'fa-light fa-comments',
            'topicViews'                                => 'fa-light fa-eye',
            'topicRepliesReplyPresent'                  => 'fa-light fa-comments',
            'topicRepliesReplyMissing'                  => 'fa-light fa-comments',
            'topicVotesVotePresent'                     => 'fa-light fa-thumbs-up',
            'topicVotesVoteMissing'                     => 'fa-light fa-thumbs-up',
            'topicPages'                                => 'fa-light fa-file',
            'topicActionGoToStart'                      => 'fa-light fa-backward-fast',
            'topicShare'                                => 'fa-light fa-share-nodes',

            // topic log
            'topicLogUserAgent'                         => 'fa-light fa-globe',
            'topicLogUserFingerprint'                   => 'fa-light fa-info',
            'topicLogUserIp'                            => 'fa-light fa-laptop',

            // topic post
            'postSubscribe'                             => 'fa-light fa-bell',
            'postSubscribed'                            => 'fa-light fa-bell-slash',
            'postShare'                                 => 'fa-light fa-share-nodes',
            'postComment'                               => 'fa-light fa-comment',
            'postShareCopyUrl'                          => 'fa-light fa-copy',
            'postCommentActive'                         => 'fa-light fa-comment',
            'postDelete'                                => 'fa-light fa-trash-can',
            'postRestore'                               => 'fa-light fa-arrow-rotate-left',
            'postMentionAuthor'                         => 'fa-light fa-at',
            'postAnswerQuote'                           => 'fa-light fa-comment',
            'postReport'                                => 'fa-light fa-flag',
            'postMenuDropdown'                          => $genericDropdown,
            'postMergeWithPrevious'                     => 'fa-light fa-arrow-up-from-bracket',
            'postBanAuthor'                             => 'fa-light fa-user-slash',
            'postDeleted'                               => 'fa-light fa-trash-can',
            'postAuthorBlocked'                         => 'fa-light fa-user-slash',
            'postAccept'                                => $faTick,
            'postAcceptAccepted'                        => 'fa-light fa-circle-check',
            'postEditHistoryShow'                       => 'fa-light fa-up-right-from-square',
            'postFoldedCommentsUnfold'                  => 'fa-light fa-comments',
            'postVote'                                  => 'fa-light fa-thumbs-up',
            'postVoted'                                 => 'fa-solid fa-thumbs-up',
            'postAssetDownload'                         => 'fa-light fa-download',
            'postEdit'                                  => 'fa-light fa-pen-to-square',
            'postWasRead'                               => 'fa-light fa-file',
            'postWasNotRead'                            => null, // icon generated by css
            'postVisitPost'                             => 'fa-light fa-share',
            'postVisitThread'                           => 'fa-light fa-share',
            'postCopyLinkPost'                          => 'fa-light fa-link',
            'postCopyLinkThread'                        => 'fa-light fa-link',

            // tree topic
            'postGuiderailExpanded'                     => 'fa-solid fa-plus',
            'postGuiderailCollapsed'                    => 'fa-solid fa-minus',
            'treeTopicPostOrder'                        => 'fa-light fa-arrow-down-z-a',
            'treeTopicSeeFull'                          => 'fa-light fa-list-tree',
            'treeTopicSeePrevious'                      => 'fa-light fa-arrow-up-left',
            'treeTopicPostTarget'                       => 'fa-light fa-arrow-turn-left',

            // post history
            'postHistoryVersion'                        => 'fa-light fa-file',
            'postHistoryVersionRestore'                 => 'fa-light fa-arrow-rotate-left',
            'postHistoryVersionShow'                    => 'fa-light fa-eye',

            // post comment
            'postCommentEdit'                           => 'fa-light fa-pencil',
            'postCommentDelete'                         => 'fa-light fa-trash-can',
            'postCommentConvertToPost'                  => 'fa-light fa-compress',
            'postCommentReport'                         => 'fa-light fa-flag',
            'postCommentAuthorBlocked'                  => 'fa-light fa-user-slash',

            // profile
            'profileReputationHistory'                  => 'fa-light fa-chart-line',
            'profileReputationActivity'                 => 'fa-light fa-list-check',
            'profileReputationGain'                     => 'fa-light fa-level-up',
            'profileReputationLose'                     => 'fa-light fa-level-down',
            'profileActions'                            => 'fa-light fa-handshake-simple',
            'profileUserSendMessage'                    => 'fa-light fa-envelope',
            'profileUserFindPosts'                      => 'fa-light fa-user-magnifying-glass',
            'profileUserBan'                            => 'fa-light fa-user-lock',
            'profileUserShowInAdmin'                    => 'fa-light fa-eye',
            'profileMenuDropdown'                       => $genericDropdown,
            'profileUserBlock'                          => 'fa-light fa-lock',
            'profileUserUnblock'                        => 'fa-light fa-unlock',
            'profileUserResidence'                      => 'fa-light fa-location-dot',
            'profileUserWebsite'                        => 'fa-light fa-globe',
            'profileUserGithub'                         => 'fa-brands fa-github',
            'profileUserLastVisitDate'                  => 'fa-light fa-right-to-bracket',
            'profileUserRegisterDate'                   => 'fa-light fa-user-plus',
            'profileUserVisits'                         => 'fa-light fa-eye',
            'profileUserAge'                            => 'fa-light fa-calendar-days',
            'profileUserMicroblogs'                     => 'fa-light fa-address-book',
            'profileUserPermissions'                    => 'fa-light fa-medal',
            'profileUserPermissionGranted'              => $faTick,
            'profileUserPermissionDenied'               => $faCross,

            // job board
            'jobBoardLoading'                           => $genericLoading,
            'jobBoardFilterRemote'                      => 'fa-light fa-wifi',
            'jobBoardFilterSalary'                      => 'fa-light fa-credit-card',
            'jobBoardPackageBenefit'                    => $faTick,
            'jobBoardSearch'                            => 'fa-light fa-magnifying-glass',
            'jobBoardSearchLocation'                    => 'fa-light fa-location-dot',
            'jobBoardSubscribedOffers'                  => 'fa-light fa-heart',
            'jobBoardPricingPlanBulletPoint'            => 'fa-light fa-check',
            'jobBoardPricingPlanName'                   => 'fa-light fa-share-nodes',

            // job landing
            'jobLandingBenefit'                         => 'fa-light fa-circle-check',
            'jobLandingCommunity'                       => 'fa-light fa-users',
            'jobLandingForumIt'                         => 'fa-light fa-graduation-cap',
            'jobLandingKnowledgeShareEstablishmentYear' => 'fa-light fa-comments',
            'jobLandingMonthlyViews'                    => 'fa-light fa-arrow-pointer',

            // job offer
            'jobOfferAdditionalQuestions'               => 'fa-light fa-circle-info',
            'jobOfferBack'                              => 'fa-light fa-backward',
            'jobOfferBenefit'                           => $faTick,
            'jobOfferComments'                          => 'fa-light fa-comment',
            'jobOfferContract'                          => 'fa-light fa-handshake',
            'jobOfferDetails'                           => 'fa-light fa-circle-info',
            'jobOfferDraftSave'                         => 'fa-light fa-floppy-disk',
            'jobOfferDraftTabNext'                      => 'fa-light fa-angle-right',
            'jobOfferDraftTabPrev'                      => 'fa-light fa-angle-left',
            'jobOfferEdit'                              => 'fa-light fa-pen-to-square',
            'jobOfferExpiresInDays'                     => 'fa-light fa-clock',
            'jobOfferFeaturePresent'                    => $faTick,
            'jobOfferFeatureMissing'                    => $faCross,
            'jobOfferLocation'                          => 'fa-light fa-location-dot',
            'jobOfferMoreLikeThis'                      => 'fa-light fa-star',
            'jobOfferNew'                               => 'fa-light fa-pencil',
            'jobOfferPaymentRequired'                   => 'fa-light fa-credit-card',
            'jobOfferPublishDate'                       => 'fa-light fa-calendar-days',
            'jobOfferRemove'                            => 'fa-light fa-trash-can',
            'jobOfferReport'                            => 'fa-light fa-flag',
            'jobOfferRequirementRank'                   => 'fa-light fa-circle',
            'jobOfferRequirementRanked'                 => 'fa-solid fa-circle',
            'jobOfferSeniority'                         => 'fa-light fa-fw fa-chart-line',
            'jobOfferSubscribe'                         => 'fa-light fa-star',
            'jobOfferSubscribed'                        => 'fa-solid fa-star',
            'jobOfferViews'                             => 'fa-light fa-eye',
            'jobOfferApplications'                      => 'fa-light fa-envelope',
            'jobOfferCompanyEmployees'                  => 'fa-light fa-users',
            'jobOfferCompanyEstablishmentYear'          => 'fa-light fa-calendar',
            'jobOfferCompanyWebsite'                    => 'fa-light fa-link',
            'jobOfferLocationAdd'                       => 'fa-light fa-plus',
            'jobOfferLocationRemove'                    => 'fa-light fa-circle-minus',
            'jobOfferBenefitPresent'                    => $faTick,
            'jobOfferBenefitMissing'                    => $faCross,
            'jobOfferBenefitCustom'                     => $faTick,
            'jobOfferBenefitRemove'                     => 'fa-light fa-circle-minus',
            'jobOfferFirmNameAdd'                       => 'fa-light fa-circle-plus',
            'jobOfferMissingLogo'                       => 'fa-light fa-laptop-code',

            // job offer bundle
            'jobOfferBundlePlan'                        => 'fa-light fa-share-nodes',
            'jobOfferBundleRemaining'                   => 'fa-light fa-cart-circle-arrow-down',
            'jobOfferBundleContinue'                    => 'fa-light fa-copy',

            // job board filters
            'jobBoardFilterOpen'                        => 'fa-light fa-angle-down',
            'jobBoardFilterClosed'                      => 'fa-light fa-angle-up',

            // job offer comment
            'jobOfferCommentEdit'                       => 'fa-light fa-pen-to-square',
            'jobOfferCommentDelete'                     => 'fa-light fa-trash-can',
            'jobOfferCommentMenuDropdown'               => $genericDropdown,

            // job offer pricing
            'pricingSelected'                           => 'fa-light fa-circle-check',
            'pricingBenefitPresent'                     => 'fa-light fa-circle-check',
            'pricingBenefitMissing'                     => $faCross,
            'pricingHelpExample'                        => 'fa-light fa-circle-question',

            // application
            'jobOfferApplicationExternal'               => 'fa-light fa-arrow-up-right-from-square',

            // vcard
            'vCardLastVisitDate'                        => 'fa-light fa-right-to-bracket',
            'vCardPosts'                                => 'fa-light fa-comments',
            'vCardRegisterDate'                         => 'fa-light fa-user-plus',
            'vCardReputation'                           => 'fa-light fa-chart-line',
            'vCardUserBlock'                            => 'fa-light fa-user-lock',
            'vCardUserFindPosts'                        => 'fa-light fa-magnifying-glass',
            'vCardUserPrivateMessage'                   => 'fa-light fa-envelope',
            'vCardUserShowInAdmin'                      => 'fa-light fa-eye',
            'vCardUserResidence'                        => 'fa-light fa-location-dot',

            // wiki
            'wikiAttachmentRemove'                      => 'fa-light fa-trash-can',
            'wikiAuthor'                                => 'fa-light fa-person',
            'wikiAuthors'                               => 'fa-light fa-users',
            'wikiCategories'                            => 'fa-light fa-folder-open',
            'wikiCategory'                              => 'fa-light fa-paragraph',
            'wikiChildCreate'                           => 'fa-light fa-plus',
            'wikiClearCache'                            => 'fa-light fa-trash-can',
            'wikiCommentEdit'                           => 'fa-light fa-pen-to-square',
            'wikiCommentRemove'                         => 'fa-light fa-trash-can',
            'wikiComments'                              => 'fa-light fa-comments',
            'wikiCopyCreate'                            => 'fa-light fa-clone',
            'wikiCopyRemove'                            => 'fa-light fa-chain-broken',
            'wikiEdit'                                  => 'fa-light fa-pen-to-square',
            'wikiMove'                                  => 'fa-light fa-circle-right',
            'wikiRemove'                                => 'fa-light fa-trash-can',
            'wikiSubscribe'                             => 'fa-light fa-bell',
            'wikiCreateDate'                            => 'fa-light fa-calendar-days',
            'wikiViews'                                 => 'fa-light fa-eye',
            'wikiLastUpdateDate'                        => 'fa-light fa-calendar',
            'wikiRecentChanges'                         => 'fa-light fa-calendar-o',
            'wikiMoreLikeThis'                          => 'fa-light fa-eye',
            'wikiRelated'                               => 'fa-light fa-thumbtack',
            'wikiVersionCompare'                        => 'fa-light fa-calendar-o',
            'wikiVersionRestore'                        => 'fa-light fa-arrow-rotate-left',
            'wikiVersionsAndAuthors'                    => 'fa-light fa-clock-rotate-left',
            'wikiEditorActionAnchor'                    => 'fa-light fa-link',
            'wikiEditorActionBold'                      => 'fa-light fa-bold',
            'wikiEditorActionCodeBlock'                 => 'fa-light fa-code',
            'wikiEditorActionHeading'                   => 'fa-light fa-heading',
            'wikiEditorActionImage'                     => 'fa-light fa-image',
            'wikiEditorActionInlineCode'                => 'fa-light fa-text-width',
            'wikiEditorActionItalics'                   => 'fa-light fa-italic',
            'wikiEditorActionListOrdered'               => 'fa-light fa-list-ol',
            'wikiEditorActionListUnordered'             => 'fa-light fa-list-ul',
            'wikiEditorActionQuote'                     => 'fa-light fa-quote-left',
            'wikiEditorActionTable'                     => 'fa-light fa-table',
            'wikiEditorActionUnderline'                 => 'fa-light fa-underline',
            'wikiEditorHelp'                            => 'fa-light fa-question',

            // survey
            'surveyExperiment'                          => 'fa-light fa-flask',
            'surveyExperimentNew'                       => 'fa-light fa-plus',
            'surveyExperimentBack'                      => 'fa-light fa-arrow-left',
            'surveyExperimentMemberRemove'              => 'fa-light fa-trash',
            'surveyExperimentMembersSave'               => $faTick,

            // admin panel
            'adminTickMark'                             => $faTick,
            'adminCrossMark'                            => $faCross,
            'adminMaterialPost'                         => 'fa-light fa-arrow-right',
            'adminMaterialPostDropdown'                 => $genericDropdown,

            // admin material
            'adminMaterialPostBack'                     => 'fa-light fa-arrow-left',
            'adminMaterialReported'                     => 'fa-light fa-flag',
            'adminMaterialSearch'                       => 'fa-light fa-magnifying-glass',

            // admin material history type
            'logItemCreated'                            => 'fa-light fa-comment', // posts, comments, microblogs
            'logItemDeleted'                            => 'fa-light fa-trash-can',
            'logItemReported'                           => 'fa-light fa-flag',
            'logReportClosed'                           => $faTick,

            // admin content marker
            'contentMarkerQuote'                        => 'fa-light fa-reply-all',
            'contentMarkerVideo'                        => 'fa-light fa-film',
            'contentMarkerTable'                        => 'fa-light fa-table',
            'contentMarkerIFrame'                       => 'fa-light fa-window-maximize',
            'contentMarkerCode'                         => 'fa-light fa-code',
            'contentMarkerImage'                        => 'fa-light fa-image',
            'contentMarkerHeading'                      => 'fa-light fa-heading',

            // admin censore
            'adminCensoreNew'                           => 'fa-light fa-plus',
            'adminCensoreRemove'                        => 'fa-light fa-trash-can',

            // admin user
            'adminUserAccountSettings'                  => 'fa-light fa-user-gear',
            'adminUserFindFingerprints'                 => 'fa-light fa-fingerprint',
            'adminUserFindInLog'                        => 'fa-light fa-newspaper',
            'adminUserGroupSettings'                    => 'fa-light fa-users',
            'adminUserMicroblogs'                       => 'fa-light fa-comments',
            'adminUserPostComments'                     => 'fa-light fa-comments',
            'adminUserPosts'                            => 'fa-light fa-comment',
            'adminUserReportReceived'                   => 'fa-light fa-flag',
            'adminUserReportSent'                       => 'fa-light fa-flag',
            'adminUserSettings'                         => 'fa-light fa-gear',
            'adminUserShowProfile'                      => 'fa-light fa-user',
            'adminUserStatistics'                       => 'fa-light fa-chart-line',
            'adminUserStatisticsAllTime'                => 'fa-light fa-calendar-check',
            'adminUserStatisticsLastDay'                => 'fa-light fa-calendar-day',
            'adminUserStatisticsLastMonth'              => 'fa-light fa-calendar-days',
            'adminUserStatisticsLastWeek'               => 'fa-light fa-calendar-week',
            'adminUserStatisticsLastYear'               => 'fa-light fa-calendar',

            // footer
            'footerContactUs'                           => 'fa-light fa-circle-info',
            'footerPromoteFacebook'                     => 'fa-brands fa-facebook',

            // theme toggle
            'themeToggleDark'                           => 'fa-light fa-moon',
            'themeToggleLight'                          => 'fa-light fa-sun-bright',
            'themeToggleSystem'                         => 'fa-light fa-display',

            // post review
            'postReviewClose'                           => $genericClose,

            // look&feel
            'lookAndFeel'                               => 'fa-light fa-palette',

            // navigation
            'navigationAllCategories'                   => 'fa-solid fa-chevron-right',
            'navigationSectionDiscussion'               => 'fa-light fa-suitcase',
            'navigationSectionDiscussionTechnical'      => 'fa-light fa-message-code',
            'navigationSectionCommunity'                => 'fa-light fa-users',
            'navigationCategoryResources'               => 'fa-light fa-book-open',
            'navigationActiveDiscussions'               => 'fa-light fa-message',
            'navigationOnlineUsers'                     => 'fa-light fa-arrow-trend-up',
            'navigationCategoryPromoted'                => 'fa-solid fa-star',
            'navigationCategoryTrending'                => 'fa-light fa-arrow-trend-up',
            'navigationDropdown'                        => 'fa-light fa-chevron-down',
            'navigationNavigate'                        => 'fa-light fa-arrow-right',
        ];
    }
}
