$(document).ready(function () {

  var token    = localStorage.getItem('userToken');
  var userId   = localStorage.getItem('userId');
  var username = localStorage.getItem('userUsername');
  var email    = localStorage.getItem('userEmail');

  // ── Auth guard: redirect if not logged in ──
  if (!token || !userId) {
    window.location.href = 'login.html';
    return;
  }

  // ── Populate header ──
  $('#displayUsername').text(username || 'User');
  $('#displayEmail').text(email || '');
  $('#avatarInitial').text((username || 'U').charAt(0).toUpperCase());

  // ── Load existing profile data ──
  loadProfile();

  function loadProfile() {
    $.ajax({
      url: 'php/profile.php',
      type: 'GET',
      data: { action: 'get', token: token, user_id: userId },
      dataType: 'json',
      success: function (res) {
        if (res.success && res.profile) {
          var p = res.profile;
          $('#full_name').val(p.full_name  || '');
          $('#age').val(p.age             || '');
          $('#dob').val(p.dob             || '');
          $('#contact').val(p.contact     || '');
          $('#address').val(p.address     || '');
        } else if (!res.success && res.message === 'Unauthorized') {
          doLogout();
        }
      },
      error: function () {
        showError('Failed to load profile. Please refresh.');
      }
    });
  }

  // ── Save / update profile ──
  $('#profileForm').on('submit', function (e) {
    e.preventDefault();
    hideMessages();

    var data = {
      action:    'update',
      token:     token,
      user_id:   userId,
      full_name: $.trim($('#full_name').val()),
      age:       $.trim($('#age').val()),
      dob:       $('#dob').val(),
      contact:   $.trim($('#contact').val()),
      address:   $.trim($('#address').val())
    };

    // Basic validation
    if (data.age && (isNaN(data.age) || data.age < 1 || data.age > 120)) {
      showError('Please enter a valid age (1-120).');
      return;
    }

    setSaveLoading(true);

    $.ajax({
      url: 'php/profile.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      dataType: 'json',
      success: function (res) {
        setSaveLoading(false);
        if (res.success) {
          showSuccess('Profile updated successfully!');
        } else if (res.message === 'Unauthorized') {
          doLogout();
        } else {
          showError(res.message || 'Update failed.');
        }
      },
      error: function (xhr) {
        setSaveLoading(false);
        var res = safeParseJSON(xhr.responseText);
        showError(res.message || 'Server error.');
      }
    });
  });

  // ── Logout ──
  $('#logoutBtn').on('click', function () {
    // Tell backend to destroy Redis session
    $.ajax({
      url: 'php/login.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ action: 'logout', token: token }),
      dataType: 'json',
      complete: function () {
        doLogout();
      }
    });
  });

  function doLogout() {
    localStorage.removeItem('userToken');
    localStorage.removeItem('userId');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userUsername');
    window.location.href = 'login.html';
  }

  // ── Helpers ──
  function showError(msg) {
    $('#msgErrorText').text(msg);
    $('#msgError').addClass('show');
    $('html, body').animate({ scrollTop: 0 }, 300);
  }

  function showSuccess(msg) {
    $('#msgSuccessText').text(msg);
    $('#msgSuccess').addClass('show');
    $('html, body').animate({ scrollTop: 0 }, 300);
    setTimeout(function () { $('#msgSuccess').removeClass('show'); }, 3000);
  }

  function hideMessages() {
    $('#msgError').removeClass('show');
    $('#msgSuccess').removeClass('show');
  }

  function setSaveLoading(state) {
    var btn = $('#saveBtn');
    if (state) {
      btn.prop('disabled', true).html('<span class="spinner"></span>Saving...');
    } else {
      btn.prop('disabled', false).text('Save changes');
    }
  }

  function safeParseJSON(str) {
    try { return JSON.parse(str); } catch (e) { return {}; }
  }

});