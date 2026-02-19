$(document).ready(function () {

  // If already logged in, go to profile
  if (localStorage.getItem('userToken')) {
    window.location.href = 'profile.html';
  }

  $('#registerForm').on('submit', function (e) {
    e.preventDefault();
    hideMessages();

    var username        = $.trim($('#username').val());
    var email           = $.trim($('#email').val());
    var password        = $('#password').val();
    var confirmPassword = $('#confirmPassword').val();

    // ── Client-side validation ──
    if (!username || !email || !password || !confirmPassword) {
      showError('Please fill in all fields.');
      return;
    }

    if (username.length < 3) {
      showError('Username must be at least 3 characters.');
      return;
    }

    if (!isValidEmail(email)) {
      showError('Please enter a valid email address.');
      return;
    }

    if (password.length < 6) {
      showError('Password must be at least 6 characters.');
      return;
    }

    if (password !== confirmPassword) {
      showError('Passwords do not match.');
      return;
    }

    // ── Submit via AJAX ──
    setLoading(true);

    $.ajax({
      url: 'php/register.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ username: username, email: email, password: password }),
      dataType: 'json',
      success: function (res) {
        setLoading(false);
        if (res.success) {
          showSuccess('Account created! Redirecting to login...');
          setTimeout(function () {
            window.location.href = 'login.html';
          }, 1500);
        } else {
          showError(res.message || 'Registration failed. Please try again.');
        }
      },
      error: function (xhr) {
        setLoading(false);
        var res = safeParseJSON(xhr.responseText);
        showError(res.message || 'Server error. Please try again.');
      }
    });
  });

  // ── Helpers ──
  function showError(msg) {
    $('#msgErrorText').text(msg);
    $('#msgError').addClass('show');
  }

  function showSuccess(msg) {
    $('#msgSuccessText').text(msg);
    $('#msgSuccess').addClass('show');
  }

  function hideMessages() {
    $('#msgError').removeClass('show');
    $('#msgSuccess').removeClass('show');
  }

  function setLoading(state) {
    var btn = $('#registerBtn');
    if (state) {
      btn.prop('disabled', true).html('<span class="spinner"></span>Creating...');
    } else {
      btn.prop('disabled', false).text('Create account');
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function safeParseJSON(str) {
    try { return JSON.parse(str); } catch (e) { return {}; }
  }

});