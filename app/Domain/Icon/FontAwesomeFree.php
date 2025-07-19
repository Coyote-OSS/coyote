<?php
namespace Coyote\Domain\Icon;

readonly class FontAwesomeFree
{
    public function icons(): array
    {
        $faTick = 'fa-solid fa-check';
        $faCross = 'fa-solid fa-xmark';
        $faSpinner = 'fas fa-spinner';

        $genericUploading = $faSpinner;
        $genericLoading = $faSpinner;
        $genericClose = $faCross;
        $genericDropdown = 'fa fa-ellipsis';

        return [
            // global
            'scrollTop'                                 => 'fas fa-arrow-up',

            // alert
            'alertDanger'                               => 'fas fa-triangle-exclamation',
            'alertSuccess'                              => $faTick,

            // registration
            'loginGoogle'                               => 'fab fa-google',
            'loginFacebook'                             => 'fab fa-facebook',
            'loginGithub'                               => 'fab fa-github',

            // homepage
            'viewersOnlineLocal'                        => 'fas fa-eye',
            'viewersOnlineGlobal'                       => 'fas fa-users',
            'homepageActivityMicroblog'                 => 'far fa-person',
            'homepageActivityPostComment'               => 'far fa-comments',
            'homepageActivityTopic'                     => 'far fa-file',
            'homepageActivityPost'                      => 'far fa-file-lines',

            // microblog page
            'recommendedUsers'                          => 'fas fa-tag',

            // microblog
            'microblogNew'                              => 'fa-solid fa-plus',
            'microblogMine'                             => 'fa-regular fa-address-book',
            'microblogReport'                           => 'fas fa-flag',
            'microblogShare'                            => 'fas fa-share-nodes',
            'microblogAddComment'                       => 'far fa-comment',
            'microblogVote'                             => 'far fa-thumbs-up',
            'microblogVoted'                            => 'fas fa-thumbs-up',
            'microblogSubscribe'                        => 'far fa-bell',
            'microblogSubscribed'                       => 'fas fa-bell',
            'microblogFoldedUnfold'                     => 'fa fa-circle-right',
            'microblogCommentsFoldedUnfold'             => 'far fa-comments',
            'microblogBlockAuthor'                      => 'fas fa-user-slash',
            'microblogEdit'                             => 'fas fa-pen-to-square',
            'microblogDelete'                           => 'fas fa-trash-can',
            'microblogRestore'                          => 'fas fa-trash-arrow-up',
            'microblogSponsored'                        => 'fas fa-dollar-sign',
            'microblogMenuDropdown'                     => $genericDropdown,

            // microblog comment
            'microblogCommentEdit'                      => 'fas fa-pen-to-square',
            'microblogCommentDelete'                    => 'fas fa-trash-can',
            'microblogCommentRestore'                   => 'fas fa-trash-arrow-up',
            'microblogCommentBlockAuthor'               => 'fas fa-user-slash',
            'microblogCommentSaveNew'                   => 'far fa-share-from-square',
            'microblogCommentSaveExisting'              => 'far fa-share-from-square',
            'microblogCommentMenuEditRemove'            => 'fa fa-bars',
            'microblogCommentMenuAnswerFlag'            => $genericDropdown,

            // navigation dropdown
            'sponsorProfile'                            => 'fa fa-medal',
            'userProfile'                               => 'fas fa-user',
            'privateMessages'                           => 'fas fa-envelope',
            'userAccount'                               => 'fas fa-gear',
            'help'                                      => 'fas fa-circle-info',
            'adminPanel'                                => 'fas fa-user-tie',
            'logout'                                    => 'fas fa-right-from-bracket',

            // navigation menu
            'mobileMenuOpen'                            => 'fa-solid fa-bars',
            'mobileMenuClose'                           => $genericClose,
            'mobileSearchClose'                         => $genericClose,

            // private messages
            'privateMessageTyping'                      => 'far fa-comment-dots',
            'privateMessagesLoading'                    => $genericLoading,
            'privateMessageDelete'                      => 'fas fa-trash-can',
            'privateMessageReadAt'                      => $faTick,
            'privateMessageMarkAsRead'                  => 'fas fa-eye',

            // user profile
            'userReputation'                            => 'fas fa-chart-line',
            'userLastVisitDate'                         => 'fas fa-right-to-bracket',
            'userRegisterDate'                          => 'fas fa-user-plus',
            'userIpAddress'                             => 'fas fa-house',
            'userVisits'                                => 'far fa-eye',

            // user account settings
            'userAccount.userAccount'                   => 'far fa-user',
            'userAccount.skills'                        => 'far fa-address-book',
            'userAccount.privateMessageList'            => 'far fa-envelope',
            'userAccount.notificationList'              => 'fas fa-bell',
            'userAccount.postVotes'                     => 'far fa-thumbs-up',
            'userAccount.postCategories'                => 'fas fa-chart-bar',
            'userAccount.postAccepts'                   => $faTick,
            'userAccount.subscribedPages'               => 'far fa-bell', // topics, jobOffers, microblogs
            'userAccount.relations'                     => 'fas fa-user-group',
            'userAccount.notificationSettings'          => 'fas fa-bell',
            'userAccount.miscellaneousSettings'         => 'fas fa-user-gear',
            'userAccount.passwordChange'                => 'fas fa-unlock-keyhole',
            'userAccount.access'                        => 'fas fa-door-open',
            'userAccount.apiTokens'                     => 'fas fa-key',
            'userAccount.accountDelete'                 => 'fas fa-trash-can',

            // following user
            'userFollow'                                => $faTick,

            // report
            'reportClose'                               => null, // &times;
            'reportType.spam'                           => 'fas fa-envelopes-bulk',
            'reportType.abusiveLanguage'                => 'fas fa-book-skull',
            'reportType.offTopic'                       => 'fas fa-wave-square',
            'reportType.category'                       => 'fas fa-table-list',
            'reportType.extortion'                      => 'fas fa-user-graduate',
            'reportType.other'                          => "far fa-flag",

            // category
            'categorySectionMenu'                       => 'fas fa-gears',
            'categorySectionMenuItemEnabled'            => $faTick,
            'categorySectionFolded'                     => 'far fa-square-plus',
            'categorySectionFold'                       => 'far fa-square-minus',
            'categorySectionMarkAsRead'                 => 'far fa-eye',
            'categorySectionMoveUp'                     => 'fas fa-caret-up',
            'categorySectionMoveDown'                   => 'fas fa-caret-down',
            'categorySectionChildWasRead'               => 'far fa-file',
            'categorySectionChildWasNotRead'            => null, // icon generated by css

            // poll
            'postPoll'                                  => 'fa fa-square-poll-horizontal',
            'postPollRemoveOption'                      => 'fas fa-circle-minus',

            // autocomplete
            'autocompleteUserShowProfile'               => 'fas fa-user',
            'autocompleteUserPrivateMessage'            => 'fas fa-comment',
            'autocompleteUserFindPosts'                 => 'fas fa-magnifying-glass',
            'autocompleteUserNoAvatar'                  => 'fa-solid fa-user',
            'autocompleteSearch'                        => 'fas fa-magnifying-glass',

            // tags
            'tag'                                       => 'fa-solid fa-tag',
            'tagRemove'                                 => $genericClose,
            'tagRank'                                   => 'fas fa-circle',
            'tagPopularInclude'                         => 'fa fa-plus',
            'tagPopularMore'                            => 'fa fa-plus',

            // asset thumbnail
            'thumbnailAssetRemove'                      => $genericClose,
            'thumbnailAssetAdd'                         => 'fas fa-circle-plus',
            'thumbnailAssetUploadedFile'                => 'far fa-file',
            'thumbnailAssetUploading'                   => $genericUploading,

            // navigation bar
            'navigationPrivateMessages'                 => 'fas fa-envelope',
            'navigationSearch'                          => 'fa fa-magnifying-glass',
            'navigationNotifications'                   => 'fas fa-bell',

            // navigation notifications
            'notificationsMarkAllAsRead'                => 'far fa-eye',
            'notificationsOpenInNewTab'                 => 'fas fa-up-right-from-square',
            'notificationsLoading'                      => $genericLoading,
            'notificationDelete'                        => $genericClose,

            // editor
            'editorMarkdownHelp'                        => 'fab fa-markdown',
            'editorAssetUpload'                         => 'far fa-image',
            'editorAssetUploading'                      => $genericUploading,
            'editorControlBold'                         => 'fas fa-bold',
            'editorControlItalics'                      => 'fas fa-italic',
            'editorControlUnderline'                    => 'fas fa-underline',
            'editorControlStrikeThrough'                => 'fas fa-strikethrough',
            'editorControlHyperlink'                    => 'fas fa-link',
            'editorControlCodeBlock'                    => 'fas fa-code',
            'editorControlImage'                        => 'fas fa-image',
            'editorControlKeyStroke'                    => 'fas fa-keyboard',
            'editorControlListOrdered'                  => 'fas fa-list-ol',
            'editorControlListUnordered'                => 'fas fa-list-ul',
            'editorControlQuote'                        => 'fas fa-quote-left',
            'editorControlTable'                        => 'fas fa-table',
            'editorControlIndentMore'                   => 'fas fa-indent',
            'editorControlIndentLess'                   => 'fas fa-outdent',
            'editorControlEmoji'                        => 'fas fa-face-smile-beam',
            'editorEmojiPickerClose'                    => $genericClose,
            'editorMarkdownHelpKeyArrowUp'              => 'fas fa-arrow-up',
            'editorMarkdownHelpKeyArrowDown'            => 'fas fa-arrow-down',
            'editorPasteLoading'                        => $genericUploading,

            // survey
            'surveyExperimentDueTime'                   => 'fa-regular fa-clock',
            'surveyExperimentChoiceModern'              => 'fa-solid fa-toggle-on',
            'surveyExperimentChoiceLegacy'              => 'fa-solid fa-toggle-off',
            'surveyExperimentOpen'                      => 'fa-solid fa-toggle-off',
            'surveyBadgeEnlarge'                        => 'fa-solid fa-chevron-left',
            'surveyBadgeShorten'                        => 'fa-solid fa-chevron-right',

            // payment
            'paymentSecureConnection'                   => 'fas fa-lock',
            'paymentNotNecessary'                       => $faTick,
            'paymentInvoiceData'                        => 'fas fa-lock',
            'paymentBackToOffer'                        => 'fas fa-angle-left',
            'paymentSaveAndPay'                         => 'fas fa-angle-right',
            'paymentCoupon'                             => 'fas fa-circle-dollar-to-slot',

            // error page
            'errorPageBackToHomepage'                   => 'fas fa-house',
            'errorPageContactUs'                        => 'fas fa-info',
            'errorPageNeedHelp'                         => 'fas fa-asterisk',

            // tags
            'tagsSubscribed'                            => 'fas fa-tag',
            'tagsSubscribedEdit'                        => 'fas fa-gear',
            'tagsPopularForum'                          => 'fas fa-tag',
            'tagsPopularMicroblog'                      => 'fas fa-tag',
            'tagsPopularLanguage'                       => 'fas fa-wrench',

            // forum
            'forumSidebarMobileMenu'                    => 'fa-solid fa-ellipsis-vertical',
            'forumChangeCategory'                       => 'fas fa-circle-arrow-right',
            'forumActions'                              => 'fa-solid fa-circle-dot',
            'forumGlobalMarkAsRead'                     => 'far fa-eye',
            'forumCategoryMarkAsRead'                   => 'far fa-eye',
            'forumTopicMarkAsRead'                      => 'far fa-eye',

            // topic
            'topicSubscribe'                            => 'far fa-bell',
            'topicSubscribed'                           => 'fas fa-bell',
            'topicLog'                                  => 'fa fa-chart-pie',
            'topicGoToBeginning'                        => 'fa-solid fa-backward-fast',
            'topicLogBackToTopic'                       => 'fas fa-backward-step',
            'topicActionRename'                         => 'fa fa-pencil',
            'topicActionMove'                           => 'fa fa-circle-arrow-right',
            'topicActionLock'                           => 'fa fa-lock',
            'topicActionUnlock'                         => 'fa fa-unlock',
            'topicAccepted'                             => $faTick,
            'topicReported'                             => 'fa fa-fire',
            'topicStateSticky'                          => 'fa-solid fa-thumbtack',
            'topicStateLocked'                          => 'fas fa-lock',
            'topicStateStandard'                        => 'far fa-comments',
            'topicViews'                                => 'far fa-eye',
            'topicRepliesReplyPresent'                  => 'fas fa-comments',
            'topicRepliesReplyMissing'                  => 'far fa-comments',
            'topicVotesVotePresent'                     => 'fas fa-thumbs-up',
            'topicVotesVoteMissing'                     => 'far fa-thumbs-up',
            'topicPages'                                => 'far fa-file',
            'topicActionGoToStart'                      => 'fa-solid fa-backward-fast',

            // topic log
            "topicLogUserAgent"                         => "fas fa-globe",
            "topicLogUserFingerprint"                   => "fas fa-info",
            "topicLogUserIp"                            => "fas fa-laptop",

            // topic post
            'postSubscribe'                             => 'far fa-bell',
            'postSubscribed'                            => 'fas fa-bell',
            'postShare'                                 => 'fas fa-share-nodes',
            'postComment'                               => 'far fa-comment',
            'postShareCopyUrl'                          => 'fa-solid fa-copy',
            'postCommentActive'                         => 'fas fa-comment',
            'postDelete'                                => 'fa fa-trash-can',
            'postRestore'                               => 'fa fa-arrow-rotate-left',
            'postMentionAuthor'                         => 'fa fa-at',
            'postAnswerQuote'                           => 'fa fa-quote-left',
            'postReport'                                => 'fa fa-flag',
            'postMenuDropdown'                          => $genericDropdown,
            'postMergeWithPrevious'                     => 'fas fa-compress',
            'postBanAuthor'                             => 'fas fa-user-slash',
            'postDeleted'                               => 'fa-solid fa-trash-can',
            'postAuthorBlocked'                         => 'fa-solid fa-user-slash',
            'postAccept'                                => $faTick,
            'postAcceptAccepted'                        => $faTick,
            'postEditHistoryShow'                       => 'fas fa-up-right-from-square',
            'postFoldedCommentsUnfold'                  => 'far fa-comments',
            'postVote'                                  => 'far fa-thumbs-up',
            'postVoted'                                 => 'fas fa-thumbs-up',
            'postAssetDownload'                         => 'fas fa-download',
            'postEdit'                                  => 'fas fa-pen-to-square',
            'postWasRead'                               => 'far fa-file',
            'postWasNotRead'                            => null, // icon generated by css

            // tree topic
            'postFolded'                                => 'fa-solid fa-chevron-down',
            'postFold'                                  => 'fa-solid fa-chevron-up',

            // post history
            'postHistoryVersion'                        => 'far fa-file',
            'postHistoryVersionRestore'                 => 'fas fa-arrow-rotate-left',
            'postHistoryVersionShow'                    => 'fas fa-eye',

            // post comment
            'postCommentEdit'                           => 'fas fa-pencil',
            'postCommentDelete'                         => 'fas fa-trash-can',
            'postCommentConvertToPost'                  => 'fas fa-compress',
            'postCommentReport'                         => 'fas fa-flag',
            'postCommentAuthorBlocked'                  => 'fa-solid fa-user-slash',

            // profile
            'profileReputationHistory'                  => 'fa-solid fa-chart-line',
            'profileReputationActivity'                 => 'fa-solid fa-list-check',
            'profileReputationGain'                     => 'fas fa-level-up',
            'profileReputationLose'                     => 'fas fa-level-down',
            'profileActions'                            => 'fa-solid fa-handshake-simple',
            'profileUserSendMessage'                    => 'far fa-envelope',
            'profileUserFindPosts'                      => 'fas fa-magnifying-glass',
            'profileUserBan'                            => 'fas fa-user-lock',
            'profileUserShowInAdmin'                    => 'fas fa-eye',
            'profileMenuDropdown'                       => $genericDropdown,
            'profileUserBlock'                          => 'fas fa-user-slash',
            'profileUserUnblock'                        => 'fas fa-user',
            'profileUserResidence'                      => 'fas fa-location-dot',
            'profileUserWebsite'                        => 'fas fa-globe',
            'profileUserGithub'                         => 'fab fa-github',
            'profileUserLastVisitDate'                  => 'fas fa-right-to-bracket',
            'profileUserRegisterDate'                   => 'fas fa-user-plus',
            'profileUserVisits'                         => 'far fa-eye',
            'profileUserAge'                            => 'fas fa-calendar-days',
            'profileUserMicroblogs'                     => 'fa-regular fa-address-book',
            'profileUserPermissions'                    => 'fa fa-medal',
            'profileUserPermissionGranted'              => $faTick,
            'profileUserPermissionDenied'               => $faCross,

            // job board
            'jobBoardLoading'                           => $genericLoading,
            "jobBoardFilterRemote"                      => "fas fa-wifi",
            "jobBoardFilterSalary"                      => "far fa-credit-card",
            "jobBoardPackageBenefit"                    => $faTick,
            "jobBoardSearch"                            => "fas fa-magnifying-glass",
            "jobBoardSearchLocation"                    => "fas fa-location-dot",
            "jobBoardSubscribedOffers"                  => "fas fa-heart",

            // job landing
            "jobLandingBenefit"                         => "fas fa-circle-check",
            "jobLandingCommunity"                       => "fas fa-users",
            "jobLandingForumIt"                         => "fas fa-graduation-cap",
            "jobLandingKnowledgeShareEstablishmentYear" => "fas fa-comments",
            "jobLandingMonthlyViews"                    => "fas fa-arrow-pointer",

            // job offer
            "jobOfferAdditionalQuestions"               => "fas fa-circle-info",
            "jobOfferBack"                              => "fas fa-backward",
            "jobOfferBenefit"                           => $faTick,
            "jobOfferComments"                          => "far fa-comment",
            "jobOfferContract"                          => "far fa-handshake",
            "jobOfferDetails"                           => "fas fa-circle-info",
            "jobOfferDraftSave"                         => "fas fa-floppy-disk",
            "jobOfferDraftTabNext"                      => "fas fa-angle-right",
            "jobOfferDraftTabPrev"                      => "fas fa-angle-left",
            "jobOfferEdit"                              => "fas fa-pen-to-square",
            "jobOfferExpiresInDays"                     => "far fa-clock",
            "jobOfferFeaturePresent"                    => $faTick,
            "jobOfferFeatureMissing"                    => $faCross,
            "jobOfferLocation"                          => "fas fa-location-dot",
            "jobOfferMoreLikeThis"                      => "fas fa-star",
            "jobOfferNew"                               => "fas fa-pencil",
            "jobOfferPaymentRequired"                   => "fas fa-credit-card",
            "jobOfferPublishDate"                       => "fas fa-calendar-days",
            "jobOfferRemove"                            => "fas fa-trash-can",
            "jobOfferReport"                            => "fas fa-flag",
            "jobOfferRequirementRank"                   => 'fas fa-circle',
            "jobOfferRequirementRanked"                 => 'fas fa-circle',
            "jobOfferSeniority"                         => "fas fa-fw fa-chart-line",
            "jobOfferSubscribe"                         => "fa-regular fa-bell",
            "jobOfferSubscribed"                        => "fa-solid fa-bell",
            "jobOfferViews"                             => "far fa-eye",
            "jobOfferCompanyEmployees"                  => "fas fa-users",
            "jobOfferCompanyEstablishmentYear"          => "far fa-calendar",
            "jobOfferCompanyWebsite"                    => "fas fa-link",
            'jobOfferLocationAdd'                       => 'fas fa-plus',
            'jobOfferLocationRemove'                    => 'fa-circle-minus',
            'jobOfferBenefitPresent'                    => $faTick,
            'jobOfferBenefitMissing'                    => $faCross,
            'jobOfferBenefitCustom'                     => $faTick,
            'jobOfferBenefitRemove'                     => 'fas fa-circle-minus',
            'jobOfferFirmNameAdd'                       => 'fas fa-circle-plus',

            // job board filters
            'jobBoardFilterOpen'                        => 'fas fa-angle-down',
            'jobBoardFilterClosed'                      => 'fas fa-angle-up',

            // job offer comment
            'jobOfferCommentEdit'                       => 'fa fa-pen-to-square',
            'jobOfferCommentDelete'                     => 'fa fa-trash-can',
            'jobOfferCommentMenuDropdown'               => $genericDropdown,

            // job offer pricing
            'pricingSelected'                           => 'fa fa-circle-check',
            'pricingBenefitPresent'                     => 'fa fa-circle-check',
            'pricingBenefitMissing'                     => $faCross,
            'pricingHelpExample'                        => 'fa fa-circle-question',

            // vcard
            "vCardLastVisitDate"                        => "fas fa-right-to-bracket",
            "vCardPosts"                                => "far fa-comments",
            "vCardRegisterDate"                         => "fas fa-user-plus",
            "vCardReputation"                           => "fas fa-chart-line",
            "vCardUserBlock"                            => "fas fa-user-lock",
            "vCardUserFindPosts"                        => "fas fa-magnifying-glass",
            "vCardUserPrivateMessage"                   => "fas fa-envelope",
            "vCardUserShowInAdmin"                      => "fas fa-eye",
            'vCardUserResidence'                        => 'fas fa-location-dot',

            // wiki
            "wikiAttachmentRemove"                      => "fas fa-trash-can",
            "wikiAuthor"                                => "fas fa-person",
            "wikiAuthors"                               => "fas fa-users",
            "wikiCategories"                            => "fas fa-folder-open",
            "wikiCategory"                              => "fas fa-paragraph",
            "wikiChildCreate"                           => "fas fa-plus",
            "wikiClearCache"                            => "far fa-trash-can",
            "wikiCommentEdit"                           => "fas fa-pen-to-square",
            "wikiCommentRemove"                         => "fas fa-trash-can",
            "wikiComments"                              => "fas fa-comments",
            "wikiCopyCreate"                            => "far fa-clone",
            "wikiCopyRemove"                            => "fas fa-chain-broken",
            "wikiEdit"                                  => "fas fa-pen-to-square",
            "wikiMove"                                  => "fas fa-circle-right",
            "wikiRemove"                                => "fas fa-trash-can",
            "wikiSubscribe"                             => "fa-regular fa-bell",
            "wikiCreateDate"                            => "far fa-calendar-days",
            "wikiViews"                                 => "far fa-eye",
            "wikiLastUpdateDate"                        => "far fa-calendar",
            "wikiRecentChanges"                         => "far fa-calendar-o",
            "wikiMoreLikeThis"                          => "far fa-eye",
            "wikiRelated"                               => "fas fa-thumbtack",
            "wikiVersionCompare"                        => "far fa-calendar-o",
            "wikiVersionRestore"                        => "fas fa-arrow-rotate-left",
            "wikiVersionsAndAuthors"                    => "fas fa-clock-rotate-left",
            "wikiEditorActionAnchor"                    => "fas fa-link",
            "wikiEditorActionBold"                      => "fas fa-bold",
            "wikiEditorActionCodeBlock"                 => "fas fa-code",
            "wikiEditorActionHeading"                   => "fas fa-heading",
            "wikiEditorActionImage"                     => "fas fa-image",
            "wikiEditorActionInlineCode"                => "fas fa-text-width",
            "wikiEditorActionItalics"                   => "fas fa-italic",
            "wikiEditorActionListOrdered"               => "fas fa-list-ol",
            "wikiEditorActionListUnordered"             => "fas fa-list-ul",
            "wikiEditorActionQuote"                     => "fas fa-quote-left",
            "wikiEditorActionTable"                     => "fas fa-table",
            "wikiEditorActionUnderline"                 => "fas fa-underline",
            "wikiEditorHelp"                            => "fas fa-question",

            // survey
            'surveyExperiment'                          => 'fa fa-solid fa-flask',
            'surveyExperimentNew'                       => 'fa-solid fa-plus',
            'surveyExperimentBack'                      => 'fa-solid fa-arrow-left',
            'surveyExperimentMemberRemove'              => 'fa-solid fa-trash',
            'surveyExperimentMembersSave'               => $faTick,
            'surveyExperimentJoined'                    => 'fa-flask',
            'surveyExperimentLeft'                      => 'fa-bug-slash',
            'surveyExperimentEnabledModern'             => 'fa-toggle-on',
            'surveyExperimentEnabledLegacy'             => 'fa-toggle-off',

            // admin panel
            'adminTickMark'                             => $faTick,
            'adminCrossMark'                            => $faCross,
            'adminMaterialPost'                         => 'fas fa-arrow-right',
            'adminMaterialPostDropdown'                 => $genericDropdown,

            // admin material
            "adminMaterialPostBack"                     => "fas fa-arrow-left",
            "adminMaterialReported"                     => "far fa-flag",
            "adminMaterialSearch"                       => "fas fa-magnifying-glass",

            // admin material history type
            'logItemCreated'                            => 'far fa-comment', // posts, comments, microblogs
            'logItemDeleted'                            => 'far fa-trash-can',
            'logItemReported'                           => 'far fa-flag',
            'logReportClosed'                           => $faTick,

            // admin content marker
            'contentMarkerQuote'                        => 'fas fa-reply-all',
            'contentMarkerVideo'                        => 'fas fa-film',
            'contentMarkerTable'                        => 'fas fa-table',
            'contentMarkerIFrame'                       => 'far fa-window-maximize',
            'contentMarkerCode'                         => 'fas fa-code',
            'contentMarkerImage'                        => 'far fa-image',
            'contentMarkerHeading'                      => 'fas fa-heading',

            // admin censore
            "adminCensoreNew"                           => "fas fa-plus",
            "adminCensoreRemove"                        => "far fa-trash-can",

            // admin user
            "adminUserAccountSettings"                  => "fas fa-user-gear",
            "adminUserFindFingerprints"                 => "fas fa-fingerprint",
            "adminUserFindInLog"                        => "fa fa-newspaper",
            "adminUserGroupSettings"                    => "fa fa-users",
            "adminUserMicroblogs"                       => "far fa-comments",
            "adminUserPostComments"                     => "far fa-comments",
            "adminUserPosts"                            => "far fa-comment",
            "adminUserReportReceived"                   => "far fa-flag",
            "adminUserReportSent"                       => "fas fa-flag",
            "adminUserSettings"                         => "fa fa-gear",
            "adminUserShowProfile"                      => "fa fa-user",
            "adminUserStatistics"                       => "fas fa-chart-line",
            "adminUserStatisticsAllTime"                => "far fa-calendar-check",
            "adminUserStatisticsLastDay"                => "fas fa-calendar-day",
            "adminUserStatisticsLastMonth"              => "fas fa-calendar-days",
            "adminUserStatisticsLastWeek"               => "fas fa-calendar-week",
            "adminUserStatisticsLastYear"               => "far fa-calendar",

            // footer
            'footerContactUs'                           => 'fa fa-circle-info',
            'footerPromoteFacebook'                     => 'fab fa-facebook',

            // theme toggle
            'themeToggleDark'                           => 'fas fa-moon',
            'themeToggleLight'                          => 'fas fa-sun',
            'themeToggleSystem'                         => 'fas fa-display',

            // post review
            'postReviewClose'                           => $genericClose,
        ];
    }
}
