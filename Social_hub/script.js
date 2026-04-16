$(document).ready(function () {

    // Call the function to load notifications when the page loads
    setInterval(loadNotifications, 10000);

    // Call the function to load notifications count when the page loads
    updateNotificationCount();
    setInterval(updateNotificationCount, 10000);


    // <!-- JavaScript for handling the modal -->
    const modal = $('#edit_profile_modal');
    const editProfileBtn = $('#profile_edit_btn');
    const closeBtn = $('#close_edit_modal');

    function openModal() {
        modal.css('display', 'block');
    }

    function closeModal() {
        modal.css('display', 'none');
    }

    editProfileBtn.on('click', openModal);

    closeBtn.on('click', closeModal);


    $("#change_password_btn").on("click", function () {
        $("#change_password_modal").show();
    });

    $("#close_change_password_modal").on("click", function () {
        $("#change_password_modal").hide();
    });

    $(".profile-picture-wrapper").on("click", function () {
        $("#change_img_modal").show();
    });

    $("#close_change_img_modal").on("click", function () {
        $("#change_img_modal").hide();
    });


    // hide field
    let user_id = $('#hidden_userid').val();
    var mobileNumberRegex = /^\d{10}$/;
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (mobileNumberRegex.test(user_id)) {
        $('#profile_username_email').css('display', 'none');
    }
    if (emailRegex.test(user_id)) {
        $('#profile_username_mobile').css('display', 'none');
    }

    // Toggle notification dropdown
    $("#notification-btn").on('click', function () {
        $(".notificaton_area").toggle();
        markNotificationsAsRead()
        // Call the function to load notifications when the page loads
        loadNotifications();

    });


    // Function to mark all notifications as read
    function markNotificationsAsRead() {
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'mark_notifications_as_read'
            },
            dataType: 'json',
            success: function (response) {
                updateNotificationCount();
            },
            error: function (error) {
                console.log('Error marking notifications as read:', error);
            }
        });
    }


    // Validate signup ID on change
    $('#signup_id').on('change', function () {
        var id = $(this).val().trim();
        var mobileNumberRegex = /^\d{10}$/;
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        var isValidID = mobileNumberRegex.test(id) || emailRegex.test(id);

        if (!isValidID) {
            $('#signup_id_error').text('Invalid signup ID').show();
        } else {
            $('#signup_id_error').hide();
        }

        return isValidID;
    });


    // Validate age on change
    $('#signup_dob').on('change', function () {
        var dob = $(this).val().trim();
        var dobDate = new Date(dob);
        var currentDate = new Date();
        var ageInMilliseconds = currentDate - dobDate;
        var ageInYears = ageInMilliseconds / (1000 * 60 * 60 * 24 * 365.25); // approximate number of days in a year
        var isValidAge = ageInYears >= 18;

        if (!isValidAge) {
            $('#signup_dob_error').text('You must be at least 18 years old to sign up').show();
        } else {
            $('#signup_dob_error').hide();
        }

        return isValidAge;
    });


    // Function to execute when welcome.php load
    if (window.location.pathname.includes('welcome.php')) {

        // Display all posts
        getAllPost();

        // Set interval to call DeleteOldStories every 1 minute (60000 milliseconds)
        setInterval(DeleteOldStories, 60000);

        // Set interval to call getStories every 1 minute (60000 milliseconds)
        setInterval(getStories, 60000);

        // Function to check and delete old story when page load
        DeleteOldStories();

    }

    // IMAGE/VIDEO PREVIEW LOGIC
    $('.preview-input').on('change', function () {
        const file = this.files[0];
        const previewSelector = $(this).data('preview');
        const previewBox = $(previewSelector);

        if (file) {
            const reader = new FileReader();
            const fileType = file.type;

            reader.onload = function (e) {
                previewBox.empty();
                if (fileType.includes('image')) {
                    previewBox.append(`<img src="${e.target.result}" alt="Preview">`);
                } else if (fileType.includes('video')) {
                    previewBox.append(`<video src="${e.target.result}" controls></video>`);
                }
                previewBox.fadeIn();
            };

            reader.readAsDataURL(file);
        } else {
            previewBox.fadeOut().empty();
        }
    });


    // Function for Display all posts
    function getAllPost() {

        $.ajax({
            url: "ajax.php",
            type: "POST",
            data: { action: "get_all_posts" },
            success: function (response) {
                let res = JSON.parse(response);

                if (res.success) {
                    $("#postsContainer").html(res.html);

                }
                else {
                    $("#postsContainer").prepend(res.error.no_posts);
                }

            },
            error: function () {
                alert("Error occurred while fetching posts.");
            }
        });
    }

    // Global Video Hover Auto-play Logic
    $(document).on('mouseenter', '.post_media_area video', function () {
        this.muted = true; // Ensure muted to bypass browser auto-play restrictions
        this.play().catch(error => {
            console.log("Auto-play prevented:", error);
        });
    }).on('mouseleave', '.post_media_area video', function () {
        this.pause();
    });

    // Show Pending Friend Request on Friends Page
    if (window.location.pathname.includes('friends.php')) {
        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {
                action: 'Show_Pending_frnd_req_card',
            },
            dataType: "json",
            success: function (response) {
                $('#frnd_req_card').empty();
                if (response.success) {
                    $('#frnd_req_card').append(response.html);
                } else {
                    $('#frnd_req_card').append(response.no_req);
                }
            },
        });
    }


    // ADD NEW POST
    $("#add_post_btn").click(function () {
        var form_data = new FormData($("#addPostForm")[0]);
        form_data.append("action", "add_post");

        $.ajax({
            url: "ajax.php",
            type: "POST",
            data: form_data,
            processData: false,
            contentType: false,
            success: function (response) {

                var res = JSON.parse(response);

                if (res.success) {
                    $("#addPostForm")[0].reset();
                    getAllPost();
                    $('#add').click();
                    $(".add_post").slideUp();
                }
                else if (res.error.media != '') {
                    alert(res.error.media);
                }
                else {
                    alert(res.error.insert_error);
                }

            },

        });
    });


    // Delete Post
    $(document).on("click", '.postDelete', function () {
        if (confirm('Are you sure to delete this post?')) {
            var postId = $(this).data("postid");
            $.ajax({
                url: "ajax.php",
                type: "POST",
                data: { action: "delete_post", post_id: postId },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        $("#card_" + postId).remove();
                    } else {
                        alert("Error deleting the post.");
                    }
                },

                error: function () {
                    alert("Error occurred while processing the request.");
                }
            });
        }
    });


    // Get gender value in edit profile form
    $('#profile_edit_btn').on('click', function () {
        var genderValue = $('#hidden_gender').val();
        console.log(genderValue);
        if (genderValue === "Female") {
            $('#edit_female').prop('checked', true);
        } else if (genderValue === "Male") {
            $('#edit_male').prop('checked', true);
        } else {
            $('#edit_other').prop('checked', true);
        }
        openModal();
    });


    // Change Password 
    $("#change_password_form").on("submit", function (e) {

        e.preventDefault()

        var currentPassword = $("#current_password").val();
        var newPassword = $("#new_password").val();
        var confirmPassword = $("#confirm_password").val();

        if (currentPassword === "" || newPassword === "" || confirmPassword === "") {
            alert("Please fill in all the fields.");
            return;
        }

        if (newPassword !== confirmPassword) {
            alert("New password and confirm password do not match.");
            return;
        }

        $.ajax({
            url: "ajax.php",
            type: "POST",
            data: {
                action: "verify_password",
                current_password: currentPassword,
                new_password: newPassword
            },
            dataType: "json",
            success: function (response) {
                console.log(response);
                if (response.success) {
                    alert(response.message);
                    window.location.reload()
                }
                else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("Error occurred while verifying the password.");
            }
        });
    });


    // Change profile picture
    $('#change_img_form').on('submit', function (event) {
        event.preventDefault();

        var profileImageFile = $('#change_image').prop('files')[0];

        if (!profileImageFile) {
            alert('Please select an image.');
            return;
        } else {
            var formData = new FormData(this);
            // formData.append('profileImageFile', profileImageFile);

            $.ajax({
                type: 'POST',
                url: 'update_profile_picture.php',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response && response.status === 'success') {
                        alert(response.message);
                        location.reload();
                    } else {
                        alert('Invalid response from the server.'); // Display error message
                    }
                },
                error: function (xhr, status, error) {
                    // Handle the error, e.g., show error message
                    console.log(error); // For debugging purposes
                    alert('Error updating profile picture');
                }
            });

        }
    });


    // Accept friend request
    $(document).on('click', '.frnd_req_acc', function () {
        var request_id = $(this).closest('.pen_frnd_req_card').data('sender-id');
        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {
                action: 'Accept_Friend_Request_pp',
                request_id: request_id,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("Error occurred while accepting friend request.");
            }
        });
    });


    // Reject friend request
    $(document).on('click', '.frnd_req_rej', function () {
        var request_id = $(this).closest('.pen_frnd_req_card').data('sender-id');

        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {
                action: 'Reject_Friend_Request_pp',
                request_id: request_id,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("Error occurred while rejecting friend request.");
            }
        });
    });


    // Function to handle the Add Friend, Cancel Request, and Remove Friend buttons of other uesr profile page
    $(document).on('click', '.add_friend_btn', function () {
        var userSrno = $(this).data('user-srno');
        var requestStatus = $(this).data('request-status');

        if (requestStatus === 'not_sent') {
            // Send Friend Request
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    action: 'Send_Friend_Request',
                    receiver_id: userSrno,
                },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        // Refresh the page after sending the request
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert("Error occurred while sending friend request.");
                }
            });
        } else if (requestStatus === 'pending' || requestStatus === 'friend') {
            // Cancel Friend Request or Remove Friend
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    action: 'Cancel_Friend_Request_Or_Remove_Friend',
                    receiver_id: userSrno,
                },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        // Refresh the page after canceling request or removing friend
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert("Error occurred while processing your request.");
                }
            });
        } else if (requestStatus === 'accept_reject') {
            // Accept Friend Request (Accept button)
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    action: 'Accept_Friend_Request',
                    sender_id: userSrno,
                },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        // Refresh the page after accepting friend request
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
                error: function () {
                    alert("Error occurred while accepting friend request.");
                }
            });
        }
    });


    // Function to handle the Reject button of other uesr profile page
    $(document).on('click', '#reject_frnd', function () {
        var userSrno = $(this).data('user-srno');
        $.ajax({
            type: "POST",
            url: "ajax.php",
            data: {
                action: 'Reject_Friend_Request',
                sender_id: userSrno,
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Refresh the page after rejecting friend request
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function () {
                alert("Error occurred while rejecting friend request.");
            }
        });
    });


    // "Like" button on click functionality 
    $('#postsContainer').on('click', '.like', function () {
        var postCard = $(this).closest('.card');
        var postId = postCard.attr('data-postid');

        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'like_post',
                post_id: postId
            },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    const likeCount = data.likeCount;
                    const likeCountContainer = postCard.find('.like_count p');

                    if (likeCount > 0) {
                        const likeText = (likeCount === 1) ? 'like' : 'likes';
                        likeCountContainer.html(`<b>${likeCount} ${likeText}</b>`).show();
                    } else {
                        likeCountContainer.hide();
                    }

                    const heartIcon = postCard.find('.post-like-heart');
                    if (data.action === 'liked') {
                        heartIcon.removeClass('far').addClass('fas liked-red');
                    } else if (data.action === 'unliked') {
                        heartIcon.removeClass('fas liked-red').addClass('far');
                    }
                } else {
                    alert('Failed to like the post.');
                }
            },
            error: function (error) {
                alert('Error occurred:', error);
            }
        });
    });


    // "Comment" button on click functionality
    $('#postsContainer').on('click', '.comment', function () {
        const postCard = $(this).closest('.card');
        const commentInputBox = postCard.find('.comment_mng_area');

        // Check if the comment input box is hidden or visible
        if (commentInputBox.is(':hidden')) {
            // Slide down the comment input box if it's hidden
            commentInputBox.slideDown();
        } else {
            // Slide up the comment input box if it's visible
            commentInputBox.slideUp();
        }
    });


    // "Add Comment"button on click functionality 
    $('#postsContainer').on('click', '.add-comment-btn', function () {
        const postCard = $(this).closest('.card');
        const postId = postCard.attr('data-postid');
        const commentTextArea = postCard.find('.comment-textarea');
        const commentText = commentTextArea.val().trim();
        if (commentText !== '') {
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    action: 'add_comment',
                    post_id: postId,
                    comment_text: commentText
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const commentsSection = postCard.find('.comments-section');

                        commentsSection.prepend(response.html_comment);
                        commentTextArea.val('');
                    } else {
                        alert('Failed to add comment.');
                    }
                },
                error: function (error) {
                    alert('Error occurred:', error);
                }
            });
        }
    });

    // "Delete Comment" button on click functionality 
    $(document).on('click', '.commentDelete', function () {

        if (confirm('Are you sure to delete comment?')) {
            var commentCard = $(this).closest('.comment-card');

            var commentId = $(this).data('commentid');

            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    action: 'delete_comment',
                    comment_id: commentId
                },
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        // Remove the deleted comment from the UI
                        commentCard.remove();
                    } else {
                        alert('Failed to delete the comment.');
                    }
                },
                error: function (error) {
                    alert('Error occurred:', error);
                }
            });
        }
    });

    // Handle comment like click
    $(document).on('click', '.comment-like-icon', function () {
        var $this = $(this);
        var commentId = $this.data('commentid');
        var postCard = $this.closest('.card');
        var postId = postCard.attr('data-postid');

        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'like_comment',
                post_id: postId,
                comment_id: commentId
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var likeCountSpan = $this.closest('.comment-card').find('.comment-like-count');

                    if (response.action === 'liked') {
                        $this.removeClass('far').addClass('fas active');
                    } else {
                        $this.removeClass('fas active').addClass('far');
                    }

                    if (response.likeCount > 0) {
                        var likeText = (response.likeCount == 1) ? 'like' : 'likes';
                        likeCountSpan.text(response.likeCount + ' ' + likeText).show();
                    } else {
                        likeCountSpan.hide();
                    }
                }
            }
        });
    });


    // --- Comment Edit Functionality ---
    $(document).on('click', '.commentEdit', function () {
        var commentCard = $(this).closest('.comment-card');
        var commentBio = commentCard.find('.comment-user-bio');
        var userNameBubble = commentCard.find('.comment-user-name');
        var commentTextDiv = commentCard.find('.comment_card_text');
        var originalText = commentTextDiv.find('p').text().trim();

        // Prevent multiple edit boxes
        if (commentCard.hasClass('editing')) return;
        commentCard.addClass('editing');

        var editHtml = `
            <div class="comment-edit-box">
                <textarea class="edit-comment-input">${originalText}</textarea>
                <div class="edit-actions">
                    <span class="save-comment-btn" data-oldtext="${originalText}">Save</span>
                    <span class="cancel-comment-btn" data-oldtext="${originalText}">Cancel</span>
                </div>
            </div>
        `;

        // Hide original text and insert edit box after the bubble
        commentTextDiv.hide();
        userNameBubble.append('<div class="edit-textarea-container"></div>');
        userNameBubble.find('.edit-textarea-container').append(commentCard.find('.comment-edit-box textarea') || ''); // This is placeholder logic, let's just use the HTML string

        // Re-implementing more cleanly:
        userNameBubble.find('.edit-textarea-container').remove(); // Clear any residue

        var textareaHtml = `<textarea class="edit-comment-input">${originalText}</textarea>`;
        var actionsHtml = `
            <div class="edit-actions">
                <span class="save-comment-btn" data-oldtext="${originalText}">Save</span>
                <span class="cancel-comment-btn" data-oldtext="${originalText}">Cancel</span>
            </div>
        `;

        commentTextDiv.after(textareaHtml);
        userNameBubble.after(actionsHtml);

        commentCard.find('.comment-actions').hide();

        // Auto-resize textarea to fit content
        var textarea = userNameBubble.find('.edit-comment-input')[0];
        if (textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
            textarea.focus();
        }
    });

    // Auto-resize on input
    $(document).on('input', '.edit-comment-input', function () {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });

    $(document).on('click', '.cancel-comment-btn', function () {
        var commentCard = $(this).closest('.comment-card');
        commentCard.removeClass('editing');
        commentCard.find('.edit-comment-input').remove();
        commentCard.find('.edit-actions').remove();
        commentCard.find('.comment_card_text').show();
        commentCard.find('.comment-actions').show();
    });

    $(document).on('click', '.save-comment-btn', function () {
        var commentCard = $(this).closest('.comment-card');
        var commentId = commentCard.attr('data-comment-id');
        var newText = commentCard.find('.edit-comment-input').val().trim();

        if (newText === "") return;

        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'update_comment',
                comment_id: commentId,
                comment_text: newText
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    commentCard.find('.comment_card_text p').text(newText);
                    commentCard.removeClass('editing');
                    commentCard.find('.edit-comment-input').remove();
                    commentCard.find('.edit-actions').remove();
                    commentCard.find('.comment_card_text').show();
                    commentCard.find('.comment-actions').show();
                } else {
                    alert('Error updating comment: ' + (response.error || 'Unknown error'));
                }
            },
            error: function (xhr, status, error) {
                alert('Ajax error: ' + error);
            }
        });
    });


    // Remove friend from friend.php and profile page
    $(document).on('click', '.frnd_remove_btn', function () {
        var requestId = $(this).closest('.frnd_card').data('request-id');
        if (!confirm('Are you sure you want to remove this friend?')) return;
        $.ajax({
            url: 'ajax.php',
            method: 'POST',
            data: {
                action: 'remove_frnd_frndlst',
                requestId: requestId
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    console.error(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error(error);
            }
        });
    });


    // Search input keyup event handler
    $('#searchInput').on('keyup', function () {
        var searchQuery = $(this).val().trim();
        if (searchQuery.length > 0) {
            // AJAX request to get the filtered friend list based on search query
            $.ajax({
                url: 'ajax.php',
                method: 'POST',
                data: {
                    action: 'search_friends',
                    searchQuery: searchQuery
                },
                dataType: 'json',
                success: function (response) {
                    // Display the filtered friend list
                    $('#friendListContainer').html(response.filteredFriends.join(''));
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            // If search query is empty, display the entire friend list again
            $.ajax({
                url: 'ajax.php',
                method: 'POST',
                data: {
                    action: 'get_friend_list'
                },
                dataType: 'json',
                success: function (response) {
                    $('#friendListContainer').html(response.html_friend1.join('') + response.html_friend2.join(''));
                },
                error: function (xhr, status, error) {
                    console.error(error);
                }
            });
        }
    });


    // // JavaScript for scrolling the carousel
    const storyArea = $('.story');

    // Variables to store the initial mouse position and scroll position
    let initialMousePos = 0;
    let initialScrollPos = 0;

    // Function to start tracking mouse movement
    function startDrag(event) {
        initialMousePos = event.clientX;
        initialScrollPos = storyArea.scrollLeft();
        $(document).on('mousemove', dragCarousel);
        $(document).on('mouseup', stopDrag);
    }

    // Function to track mouse movement and scroll the carousel
    function dragCarousel(event) {
        const mouseX = event.clientX;
        const mouseDelta = mouseX - initialMousePos;
        storyArea.scrollLeft(initialScrollPos - mouseDelta);
    }

    // Function to stop tracking mouse movement
    function stopDrag() {
        $(document).off('mousemove', dragCarousel);
        $(document).off('mouseup', stopDrag);
    }

    // Attach mousedown event to the carousel container to start drag
    storyArea.on('mousedown', startDrag);

    // show Add post/story on click
    $("#add").click(function () {

        if ($(this).attr("class") == "hidden") {
            $("#add_story").css({ width: "80px" })
            $("#add").css({ transform: "rotate(0deg)" })
        } else {
            $("#add_story").css({ width: "270px" })
            $("#add").css({ transform: "rotate(315deg)" })
        }

        $(this).toggleClass("hidden");
        $(".add_post").slideUp();
        $(".add_story").slideUp();

    })


    // Slide-down for "Add Story" section
    $("#addStory").click(function () {
        $(".add_story").slideToggle();
        $(".add_post").slideUp();
    });

    // Slide-down for "Add Post" section
    $("#addPost").click(function () {
        $(".add_post").slideToggle();
        $(".add_story").slideUp();

    });


    // Add Story to data-base
    $("#add_story_btn").click(function () {

        const formData = new FormData($("#addStoryForm")[0]);

        formData.append("action", "add_story_database");

        const storyMedia = $("#insert_story_media")[0].files[0];

        if (storyMedia !== undefined) {
            if (storyMedia.type.startsWith('video/')) {
                const videoElement = document.createElement('video');
                videoElement.preload = 'metadata';
                videoElement.onloadedmetadata = function () {
                    const videoDuration = videoElement.duration;
                    formData.append('video_duration', videoDuration);
                    // Send AJAX request to ajax.php
                    $.ajax({
                        type: "POST",
                        url: "ajax.php",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            $("#addStoryForm")[0].reset();
                            getStories();
                            $('#add').click();
                            // $(".add_story").slideToggle();
                            alert(response);
                        },
                        error: function (xhr, status, error) {
                            console.error(xhr.responseText);
                        }
                    });
                };

                // Load the video to get the duration
                videoElement.src = URL.createObjectURL(storyMedia);
            }
            else {
                $.ajax({
                    type: "POST",
                    url: "ajax.php",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        alert(response);
                        getStories();
                        $('#add').click();
                        $("#addStoryForm")[0].reset();
                        // $(".add_story").slideToggle();
                    },
                    error: function (xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            }
        }
        else {
            alert('Please select Image or Video.')
        }
    });


    // Function to delete old storys from data base
    function DeleteOldStories() {
        $.ajax({
            type: "post",
            url: "ajax.php",
            data: {
                action: 'delete_old_story',
            },
            success: function (response) {
                getStories();
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }


    // Function to fetch the latest stories from the server
    function getStories() {
        $.ajax({
            type: "post",
            url: "ajax.php",
            data: {
                action: 'get_stories',
            },
            dataType: "json",
            success: function (response) {
                $('.story').empty();
                response.forEach(function (stories) {
                    var storyType = stories.story_type.trim();
                    if (storyType == 'video') {
                        var storyHTML = '<div class="story-item" data-story-type="video" data-story-id="' + stories.story_id + '">';
                        storyHTML += '<video src="' + stories.story_media + '"></video>'
                        storyHTML += '<div class="rounded"><img src="post_img/' + stories.user_image + '" alt=""></div>';
                        storyHTML += '<span>' + stories.story_caption + '</span>';
                        storyHTML += '</div>';
                        $('.story').append(storyHTML);
                    } else {
                        var storyHTML = '<div class="story-item" data-story-type="image" data-story-id="' + stories.story_id + '" style="background-image:url(' + stories.story_media + ');">';
                        storyHTML += '<div class="rounded"><img src="post_img/' + stories.user_image + '" alt=""></div>';
                        storyHTML += '<span>' + stories.story_caption + '</span>';
                        storyHTML += '</div>';
                        $('.story').append(storyHTML);
                    }
                });

            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    }


    // Event listner to click on story-item
    var storiesData = [];
    var currentStoryIndex = 0;
    var storyTimeout;

    $('.story').on('click', '.story-item', function () {
        $("#view_stories_modal").show();
        // Get the data-story-id attribute to identify the clicked story
        var storyId = $(this).data('story-id');

        $.ajax({
            type: "post",
            url: "ajax.php",
            data: {
                action: 'view_stories',
                storyId: storyId
            },
            dataType: "json",
            success: function (response) {
                storiesData = response;
                var startIndex = 0;
                for (var i = 0; i < response.length; i++) {
                    if (response[i].story_id == storyId) {
                        startIndex = i;
                        break;
                    }
                }
                displayStory(startIndex);
                $("#view_stories_modal").show();
            },
            error: function (xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });

    // Function to display the story 
    function displayStory(index) {
        clearTimeout(storyTimeout);
        $('.show_story_area').empty();

        // Check if index is out of bounds
        if (!storiesData || index >= storiesData.length || index === null) {
            $("#view_stories_modal").hide();
            return;
        }

        currentStoryIndex = index; // Update the global index state!

        // Get the story data at the specified index
        var story = storiesData[index];

        // Check if the story is an image or a video
        if (story.story_type.trim() === 'image') {
            // Display image
            var storyHTML = '<div class="story__container" style="background: #000;">';
            storyHTML += '<div class="story__title" style="display:flex; justify-content:space-between; width:100%; box-sizing:border-box; z-index:10; position:relative; background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%);">';
            storyHTML += '<div class="story__back-button" style="color: #000000ff;">';
            storyHTML += '<img id="back_btn" src="icon/left-arrow.png" alt="Back" style="filter: invert(1);">';
            storyHTML += '<p style="color: #000000ff; margin:0;">Story</p>';
            storyHTML += '</div>';
            storyHTML += '<div class="story__close-button" style="cursor:pointer; padding:5px; margin-right:5px; z-index:11;">';
            storyHTML += '<i class="fas fa-times" style="font-size:20px; color:#fff; text-shadow: 0 1px 3px rgba(0,0,0,0.8);"></i>';
            storyHTML += '</div>';
            storyHTML += '</div>';
            storyHTML += '<img id="story_media_view" src="' + story.story_media + '" class="img-fluid rounded-top" alt="">';
            storyHTML += '<div class="story__content" style="width:100%; box-sizing:border-box; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 100%); flex-direction: column; align-items: flex-start; display: flex; z-index: 10; position: relative;">';
            storyHTML += '<div class="story__desc" style="padding:20px 15px; width:100%; overflow-wrap:break-word;">';
            storyHTML += '<p class="story__caption" style="margin-bottom:12px; font-size:15px; color:#fff; text-shadow: 0 1px 3px rgba(0,0,0,1); line-height:1.4; font-weight: 500;">' + story.story_caption + '</p>';
            storyHTML += '<div class="story__user" style="display:flex; align-items:center;">';
            storyHTML += '<img src="post_img/' + story.user_image + '" class="story__auther" style="width:40px; height:40px; border-radius:50%; border: 2px solid #fff; margin-right:12px;" />';
            storyHTML += '<p class="story__username" style="color:#fff; font-weight:600; margin:0; font-size:15px; text-shadow: 0 1px 2px rgba(0,0,0,0.8);">' + story.user_firstname + ' ' + story.user_surname + ' <span style="font-weight:400; font-size:12px; color:#ccc; margin-left:6px;">• ' + story.time_ago + '</span></p>';
            storyHTML += '</div>';
            storyHTML += '</div>';
            storyHTML += '</div>';
            storyHTML += '<div class="story-nav-prev" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:30px; height:30px; background:rgba(0,0,0,0.4); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:5;"><i class="fas fa-chevron-left" style="color:white; font-size:16px;"></i></div>';
            storyHTML += '<div class="story-nav-next" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:30px; height:30px; background:rgba(0,0,0,0.4); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:5;"><i class="fas fa-chevron-right" style="color:white; font-size:16px;"></i></div>';
            storyHTML += '</div>';

            $('.show_story_area').append(storyHTML);

            // Add delete button if owner
            if (story.user_create_by == CURRENT_USER_SRNO) {
                $('.story__title').append('<div class="story__delete-button" data-story-id="' + story.story_id + '" style="cursor:pointer; padding:5px; margin-right:10px; z-index:11;"><i class="fas fa-trash-alt" style="font-size:18px; color:#ff4444; text-shadow: 0 1px 3px rgba(0,0,0,0.5);"></i></div>');
            }

            storyTimeout = setTimeout(function () {
                moveToNextStory();
            }, 5000);

        }
        else if (story.story_type.trim() === 'video') {
            // Display video
            var storyHTML = '<div class="story__container" style="background: #000;">';
            storyHTML += '<div class="story__title" style="display:flex; justify-content:space-between; width:100%; box-sizing:border-box; z-index:10; position:relative; background: linear-gradient(to bottom, rgba(0,0,0,0.7) 0%, transparent 100%);">';
            storyHTML += '<div class="story__back-button" style="color: #fff;">';
            storyHTML += '<img id="back_btn" src="icon/left-arrow.png" alt="Back" style="filter: invert(1);">';
            storyHTML += '<p style="color: #fff; margin:0;">Story</p>';
            storyHTML += '</div>';
            storyHTML += '<div class="story__close-button" style="cursor:pointer; padding:5px; margin-right:5px; z-index:11;">';
            storyHTML += '<i class="fas fa-times" style="font-size:20px; color:#fff; text-shadow: 0 1px 3px rgba(0,0,0,0.8);"></i>';
            storyHTML += '</div>';
            storyHTML += '</div>';
            storyHTML += '<video id="story_media_view" src="' + story.story_media + '" autoplay controls></video>';
            storyHTML += '<div class="story__content" style="width:100%; box-sizing:border-box; background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, transparent 100%); flex-direction: column; align-items: flex-start; display: flex; z-index: 10; position: relative;">';
            storyHTML += '<div class="story__desc" style="padding:20px 15px; width:100%; overflow-wrap:break-word;">';
            storyHTML += '<p class="story__caption" style="margin-bottom:12px; font-size:15px; color:#fff; text-shadow: 0 1px 3px rgba(0,0,0,1); line-height:1.4; font-weight: 500;">' + story.story_caption + '</p>';
            storyHTML += '<div class="story__user" style="display:flex; align-items:center;">';
            storyHTML += '<img src="post_img/' + story.user_image + '" class="story__auther" style="width:40px; height:40px; border-radius:50%; border: 2px solid #fff; margin-right:12px;" />';
            storyHTML += '<p class="story__username" style="color:#fff; font-weight:600; margin:0; font-size:15px; text-shadow: 0 1px 2px rgba(0,0,0,0.8);">' + story.user_firstname + ' ' + story.user_surname + ' <span style="font-weight:400; font-size:12px; color:#ccc; margin-left:6px;">• ' + story.time_ago + '</span></p>';
            storyHTML += '</div>';
            storyHTML += '</div>';
            storyHTML += '</div>';
            storyHTML += '<div class="story-nav-prev" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); width:30px; height:30px; background:rgba(0,0,0,0.4); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:5;"><i class="fas fa-chevron-left" style="color:white; font-size:16px;"></i></div>';
            storyHTML += '<div class="story-nav-next" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:30px; height:30px; background:rgba(0,0,0,0.4); border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; z-index:5;"><i class="fas fa-chevron-right" style="color:white; font-size:16px;"></i></div>';
            storyHTML += '</div>';

            $('.show_story_area').append(storyHTML);

            // Add delete button if owner
            if (story.user_create_by == CURRENT_USER_SRNO) {
                $('.story__title').append('<div class="story__delete-button" data-story-id="' + story.story_id + '" style="cursor:pointer; padding:5px; margin-right:10px; z-index:11;"><i class="fas fa-trash-alt" style="font-size:18px; color:#ff4444; text-shadow: 0 1px 3px rgba(0,0,0,0.5);"></i></div>');
            }

            // Get the video element
            var videoElement = $('#story_media_view')[0];

            // Set an event listener to move to the next story when the video finishes
            videoElement.onended = function () {
                moveToNextStory();
            };
        }
    }

    // Handle Story Deletion
    $(document).on('click', '.story__delete-button', function (e) {
        e.stopPropagation();
        var storyId = $(this).data('story-id');
        if (confirm('Are you sure you want to delete this story?')) {
            $.ajax({
                type: "POST",
                url: "ajax.php",
                data: {
                    action: 'delete_story',
                    story_id: storyId
                },
                dataType: "json",
                success: function (response) {
                    if (response.success) {
                        alert('Story deleted successfully!');
                        $("#view_stories_modal").hide();
                        // Refresh stories on page
                        if (typeof get_stories === 'function') {
                            get_stories();
                        } else {
                            location.reload();
                        }
                    } else {
                        alert('Failed to delete story: ' + response.error);
                    }
                }
            });
        }
    });

    // Function to move to the next story in the carousel
    function moveToNextStory() {
        if (currentStoryIndex === null) return;
        currentStoryIndex = (currentStoryIndex + 1);
        if (currentStoryIndex >= storiesData.length) {
            $("#view_stories_modal").hide();
            currentStoryIndex = null;
            $('.show_story_area').empty();
        } else {
            displayStory(currentStoryIndex);
        }
    }

    // Function to move to the previous story in the carousel
    function moveToPrevStory() {
        if (currentStoryIndex === null) return;
        currentStoryIndex = (currentStoryIndex - 1);
        if (currentStoryIndex < 0) {
            $("#view_stories_modal").hide();
            currentStoryIndex = null;
            $('.show_story_area').empty();
        } else {
            displayStory(currentStoryIndex);
        }
    }

    // Event listener for clicking the next / prev buttons
    $('.show_story_area').on('click', '.story-nav-next', function (e) {
        e.stopPropagation();
        clearTimeout(storyTimeout);
        moveToNextStory();
    });

    $('.show_story_area').on('click', '.story-nav-prev', function (e) {
        e.stopPropagation();
        clearTimeout(storyTimeout);
        moveToPrevStory();
    });

    // Event listener for clicking the back button
    $('.show_story_area').on('click', '#back_btn, .story__close-button', function () {
        $("#view_stories_modal").hide();
        currentStoryIndex = null;
        clearTimeout(storyTimeout);
        $('.show_story_area').empty();
    });

    // Keyboard navigation for stories
    $(document).on('keydown', function (e) {
        if ($("#view_stories_modal").is(":visible") && currentStoryIndex !== null) {
            if (e.key === "ArrowRight") {
                clearTimeout(storyTimeout);
                moveToNextStory();
            } else if (e.key === "ArrowLeft") {
                clearTimeout(storyTimeout);
                moveToPrevStory();
            } else if (e.key === "Escape") {
                $("#view_stories_modal").hide();
                currentStoryIndex = null;
                clearTimeout(storyTimeout);
                $('.show_story_area').empty();
            }
        }
    });

    // Function to load notifications
    function loadNotifications() {
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'get_notifications'
            },
            dataType: 'json',
            success: function (response) {
                var notificationsContainer = $('.notificaton_area ul');
                notificationsContainer.empty();

                if (response.notifications.length > 0) {
                    response.notifications.forEach(function (notification) {
                        var notificationItem = '<li data-notification-id="' + notification.notification_id + '" class="' + (notification.is_read ? 'read' : 'unread') + '">'
                            + '<span><b>' + notification.sender_name + '</b> ' + notification.message + '</span>'
                            + '</li>';
                        notificationsContainer.append(notificationItem);
                    });
                } else {
                    notificationsContainer.append('<li>No notifications</li>');
                }
            },
            error: function (error) {
                console.log('Error fetching notifications:', error);
            }
        });
    }


    // Function to load notifications count and delete read notification
    function updateNotificationCount() {
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'get_unread_notification_count'
            },
            success: function (data) {
                $('#notification-btn').attr('data-notification-count', data);
            },
            error: function (error) {
                console.log('Error fetching unread notification count:', error);
            }
        });

    }


    // check input message every 1 Second
    if (window.location.pathname.includes('messanger.php')) {
        setInterval(loadMessages, 1000);
    }


    // check unread message every 1 Second
    if (window.location.pathname.includes('messanger.php')) {
        setInterval(loadFriendList, 1000);
    }


    // Load friend list when messanger page load
    if (window.location.pathname.includes('messanger.php')) {
        loadFriendList();

        // Auto-open chat if friendId is in URL
        const urlParams = new URLSearchParams(window.location.search);
        const friendIdFromUrl = urlParams.get('user_srno');
        if (friendIdFromUrl) {
            // Wait a bit for the list to load
            setTimeout(function () {
                $(`.friend_card[data-friend-id="${friendIdFromUrl}"]`).click();
            }, 500);
        }
    }


    // Event listener for clicking the "Create Group" icon
    // $('#open_creat_group_form').click(function () {
    //     $('#create_group_form').slideToggle();
    // });


    // Function to handle the friend card click event
    $(document).on('click', '.friend_card', function () {
        var friendId = $(this).data('friend-id');
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'get_friend_details',
                friendId: friendId
            },
            dataType: 'json',
            success: function (response) {
                $('.chat').addClass('chat-active');
                $('.chat-header').html(response);

                // Hide people list on mobile when chat opens
                if ($(window).width() <= 768) {
                    $('.people-list').addClass('people-hidden');
                }

                loadMessages();
            },
            error: function (error) {
                console.log('Error fetching friend details:', error);
            }
        });
    });

    // Back button event listener for mobile chat
    $(document).on('click', '#chat-back-btn', function () {
        if ($(window).width() <= 768) {
            $('.chat').removeClass('chat-active');
            $('.people-list').removeClass('people-hidden');
        }
    });


    // Get friend list and chat groups
    function loadFriendList() {
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'get_friend_list_and_groups'
            },
            dataType: 'json',
            success: function (response) {
                var listContainer = $('.list');
                listContainer.empty();

                var allFriendCards = response.friendCard1.concat(response.friendCard2);
                allFriendCards.sort(function (a, b) {
                    var timeA = $(a).find('.last-message-time').text();
                    var timeB = $(b).find('.last-message-time').text();
                    return timeB.localeCompare(timeA);
                });

                allFriendCards.forEach(function (html) {
                    listContainer.append(html);
                });

                // Display chat groups and their members
                // if (response.chatGroups) {
                //     response.chatGroups.forEach(function (group) {
                //         var groupHtml = '<li class="clearfix group_card" data-group-id="' + group.group_id + '">'
                //             + '<img src="' + group.group_image + '" alt="group-icon" />'
                //             + '<div class="about">'
                //             + '<div class="name">' + group.group_name + '</div>'
                //             + '</div>'
                //             + '</li>';
                //         listContainer.append(groupHtml);
                //     });
                // }
            },
            error: function (error) {
                console.log('Error fetching friend list and groups:', error);
            }
        });
    }


    // Function to handle the group card click event
    // $(document).on('click', '.group_card', function () {
    //     var groupId = $(this).data('group-id');
    //     $.ajax({
    //         type: 'POST',
    //         url: 'ajax.php',
    //         data: {
    //             action: 'get_group_details',
    //             groupId: groupId
    //         },
    //         dataType: 'json',
    //         success: function (response) {
    //             $('.chat').css('display', 'block');
    //             $('.chat-header').html(response);
    //             loadMessages(); // You can call the function to load group messages here
    //         },
    //         error: function (error) {
    //             console.log('Error fetching group details:', error);
    //         }
    //     });
    // });

    // Function to handle sending messages
    $(document).on('click', '#send-btn', function () {
        var messageToSend = $('#message-to-send').val().trim();
        var friendId = $('.chat-with').data('friend-id');
        if (messageToSend === '') {
            return;
        }

        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'send_message',
                receiverId: friendId,
                message: messageToSend
            },
            dataType: 'json',
            success: function (response) {
                loadMessages();
            },
            error: function (error) {
                console.log('Error sending message:', error);
            }
        });

        $('#message-to-send').val('');
    });

    $(document).on('keydown', '#message-to-send', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#send-btn').click();
        }
    });


    // Function to load messages from the database and update chat history
    function loadMessages() {
        var friendId = $('.chat-with').data('friend-id');
        $.ajax({
            type: 'POST',
            url: 'ajax.php',
            data: {
                action: 'get_messages',
                friendId: friendId
            },
            dataType: 'json',
            success: function (message) {
                var chatHistory = $('.chat-history ul');
                chatHistory.empty();
                chatHistory.html(message);

                // Auto-scroll to the bottom of the chat pane
                var chatPane = $('.chat-history');
                chatPane.scrollTop(chatPane[0].scrollHeight);
            },
            error: function (error) {
                console.log('Error fetching messages:', error);
            }
        });
    }

    // Clear chat history
    $(document).on('click', '#clear_chat', function () {
        var friendId = $('.chat-with').data('friend-id');

        var confirmed = confirm("Are you sure you want to clear the chat history? It will delete messages permanently for you and your friend.");

        if (confirmed) {
            $.ajax({
                type: 'POST',
                url: 'ajax.php',
                data: {
                    action: 'clear_chat_history',
                    friendId: friendId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        loadMessages();
                    }
                },
                error: function (error) {
                    console.log('Error clearing chat history:', error);
                }
            });
        } else {

        }
    });

    // OPEN POST EDIT POPUP WITH DATA
    $(document).on('click', '.update_post', function () {
        var $this = $(this);
        var postCard = $this.closest('.card');
        var vcu_current_caption = $.trim(postCard.find('.caption_area p').text());
        var mediaArea = postCard.find('.post_media_area');
        var previewBox = $('#vcu_edit_preview_box');
        var removeBtn = $('#vcu_remove_media_btn');

        previewBox.empty();
        $('#vcu_remove_media').val('0'); // Reset removal status

        // Extract and show current media in preview
        if (mediaArea.find('img').length > 0) {
            var currentImgSrc = mediaArea.find('img').attr('src');
            previewBox.append('<img src="' + currentImgSrc + '" alt="Current Media">');
            removeBtn.show();
        } else if (mediaArea.find('video source').length > 0) {
            var currentVidSrc = mediaArea.find('video source').attr('src');
            previewBox.append('<video src="' + currentVidSrc + '" controls></video>');
            removeBtn.show();
        } else {
            previewBox.append('<div class="text-muted small">No Media Attached</div>');
            removeBtn.hide();
        }

        if ($this.data('update-id') != '') {
            $('#vcu_update_post_id').val($this.data('update-id'));
            $('#vcu_post_caption').val(vcu_current_caption);
            $('.vcu_popup_wrapper').fadeIn('slow');
        }
    });

    // HANDLE MEDIA REMOVAL IN EDIT POPUP
    $(document).on('click', '#vcu_remove_media_btn', function () {
        $('#vcu_edit_preview_box').empty().append('<div style="color: #ef4444; font-size: 0.9rem; font-weight: 500;"><i class="fas fa-info-circle me-1"></i> Media will be removed on save</div>');
        $('#vcu_remove_media').val('1'); // Flag for PHP
        $(this).fadeOut(); // Hide the remove button
        $('#vcu_post_media_input').val(''); // Clear file selection
    });

    // LIVE PREVIEW FOR EDIT POPUP MEDIA
    $('#vcu_post_media_input').on('change', function () {
        const file = this.files[0];
        const previewBox = $('#vcu_edit_preview_box');

        if (file) {
            $('#vcu_remove_media').val('0'); // Reset removal if new file picked
            $('#vcu_remove_media_btn').show(); // Show button for newer file

            const reader = new FileReader();
            reader.onload = function (e) {
                previewBox.empty();
                if (file.type.includes('image')) {
                    previewBox.append('<img src="' + e.target.result + '" alt="New Preview">');
                } else if (file.type.includes('video')) {
                    previewBox.append('<video src="' + e.target.result + '" controls></video>');
                }
            };
            reader.readAsDataURL(file);
        }
    });

    $(document).on('click', '.vcu_popup_close', function () {
        $('.vcu_popup_wrapper').fadeOut('slow');
        $('#vcu_post_media_input').val('');     // Clear file selection on close
        $('#vcu_remove_media').val('0');         // Reset remove media flag
        $('#vcu_remove_media_btn').show();       // Show remove btn again for next open
        $('#vcu_edit_preview_box').empty();      // Clear preview
    });


    // Block User
    $(document).on('click', '.user_block_btn', function () {
        if (!confirm('Are you sure you want to block this user?')) return;
        var userSrno = $(this).data('user-srno');
        $.ajax({
            url: 'ajax.php',
            method: 'POST',
            data: {
                action: 'block_user',
                user_id: userSrno
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('User blocked successfully');
                    location.reload();
                } else {
                    alert(response.message || 'Error blocking user');
                }
            }
        });
    });

    // Unblock User
    $(document).on('click', '.user_unblock_btn', function () {
        if (!confirm('Are you sure you want to unblock this user?')) return;
        var userSrno = $(this).data('user-srno');
        $.ajax({
            url: 'ajax.php',
            method: 'POST',
            data: {
                action: 'unblock_user',
                user_id: userSrno
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert('User unblocked successfully');
                    location.reload();
                } else {
                    alert(response.message || 'Error unblocking user');
                }
            }
        });
    });

});
