// Format phone numbers
function formatPhone(base, countryCode) {
    let num = base.replace(/\D/g, '');
    if (countryCode === 'KE') return `+255 ${num.slice(0,2)} ${num.slice(2,5)} ${num.slice(5)}`;
    return `+${countryCode} ${num}`;
}

const countryCode = 'KE';
const phoneBases = [
    '5925550123', '2325550198', '5325550234', '6725550456', '2125550678',
    '5725550890', '6825550901', '1225551012', '4925551123', '6425551234'
];

function replacePhone() {
    $('.phone').each(function(i, el) {
        let base = phoneBases[i % phoneBases.length];
        $(el).text(formatPhone(base, countryCode));
    });
}

// Function to handle image click - shows payment modal
function redirectToGroup() {
    $('#paymentModal').css('display', 'flex').hide().fadeIn(200);
}

// Sync dynamic page price set by Laravel into the payment modal
function syncPaymentModalContent() {
    if (typeof window.pagePrice !== 'undefined' && window.pagePrice) {
        $('#payment-title').text('Lipia Kujiunga');
        $('#payment-amount').text('TZS ' + Number(window.pagePrice).toLocaleString());
        $('#amount').val(window.pagePrice);
        $('#pay-btn').text('Lipa Sasa (TZS ' + Number(window.pagePrice).toLocaleString() + ')');
    }
}

// Messages data
const messages = [{
        type: 'text',
        number: 'Sophia',
        text: 'Nawatumia Video zangu mpya, Nimetoka Kushoot 🍑🍆..🥰❤️🥰',
        time: '23:30',
        img: 'https://static.jvhmf.site/images/18qun/adults/africa/Kodo8.jpg'
    },
    {
        type: 'video',
        number: 'Sophia',
        text: '',
        time: '23:32',
        thumb: '/template-assets/template4/images/pic1.jpg',
        size: '2.1 MB',
        img: 'https://static.jvhmf.site/images/18qun/adults/africa/Kodo8.jpg'
    },
    {
        type: 'video',
        number: 'Sophia',
        text: '',
        time: '23:33',
        thumb: '/template-assets/template4/images/pic2.jpg',
        size: '3.4 MB',
        img: 'https://static.jvhmf.site/images/18qun/adults/africa/Kodo8.jpg'
    },
    {
        type: 'video',
        number: 'C\'Élisabeth ❤❤❤',
        text: '',
        time: '23:41',
        thumb: 'https://static.jvhmf.site/images/18qun/adults/africa/video3.jpg',
        size: '8.2 MB',
        img: 'https://static.jvhmf.site/images/18qun/adults/africa/kodo5.jpg'
    },
    {
        type: 'text',
        number: 'Liliane ❤️🔥🍬',
        text: 'Vipi mpo online? Nataka nitume video mpya za Bongo',
        time: '23:30',
        img: 'https://static.jvhmf.site/images/18qun/adults/africa/kodo5.jpg'
    },
    {
        type: 'video',
        number: 'Ruto',
        text: '',
        time: '23:41',
        thumb: 'https://static.jvhmf.site/images/18qun/adults/africa/kodo6.jpg',
        size: '11.3 MB'
    },
    {
        type: 'video',
        number: '❤️‍🔥Rachel❤️‍🔥',
        text: '',
        time: '19:41',
        thumb: 'https://static.jvhmf.site/images/18qun/adults/africa/reza.jpg',
        size: '10 MB',
        forward: true,
        img: 'https://static.jvhmf.site/images/18qun/adults/africa/reza.jpg'
    },
    {
        type: 'text',
        number: 'Nuru',
        text: 'Anaetaka utamu aje inbox',
        time: '21:40'
    },
    {
        type: 'audio',
        number: 'John',
        text: '',
        time: '23:30',
        duration: '1.07'
    },
    {
        type: 'audio',
        number: 'Mary',
        text: '',
        time: '23:30',
        duration: '0.17'
    }
];

function getAvatarHtml(msg) {
    if (msg.img) {
        return `<img src="${msg.img}" alt="avatar">`;
    } else {
        return `<img src="/template-assets/template4/images/profilepic.png" alt="avatar">`;
    }
}

function renderMessage(msg, index) {
    let bubbleContent = '';

    if (msg.number) {
        bubbleContent += `<div class="number"><span class="phone"></span> ~ ${msg.number}</div>`;
    }
    if (msg.forward) {
        bubbleContent += '<div class="forward"><i class="fas fa-share"></i> Forwarded</div>';
    }

    if (msg.type === 'text') {
        bubbleContent += `<div class="message-content">${msg.text}</div>`;
    } else if (msg.type === 'video') {
        bubbleContent += `<div class="video"><img src="${msg.thumb}" onclick="redirectToGroup()" style="cursor: pointer;"><div class="details" style="justify-content:flex-start; gap:12px; margin-top:4px;"><span>${msg.size}</span><span>${msg.time}</span></div></div>`;
    } else if (msg.type === 'audio') {
        bubbleContent += `<div class="voice-note">`;
        bubbleContent += `<i class="fas fa-play-circle play-btn"></i>`;
        bubbleContent += `<div class="waveform">`;
        for (let i = 0; i < 9; i++) bubbleContent += `<span></span>`;
        bubbleContent += `</div>`;
        bubbleContent += `<span class="voice-duration">${msg.duration}</span>`;
        bubbleContent += `</div>`;
    }

    bubbleContent += `<div class="details"><span>✓✓</span><span>${msg.time}</span></div>`;

    let avatarHtml = `<div class="avatar">${getAvatarHtml(msg)}</div>`;
    let fullHtml = `<div class="message-row" id="msg-${index}" style="display:none;">${avatarHtml}<div class="user">${bubbleContent}</div></div>`;
    return fullHtml;
}

function appendMessages() {
    for (let i = 0; i < messages.length; i++) {
        $('#chat-area').append(renderMessage(messages[i], i));
    }
    replacePhone();
}

function showMessagesSequentially() {
    let i = 0;

    function showNext() {
        if (i < messages.length) {
            $('#msg-' + i).fadeIn(300);
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
            i++;
            setTimeout(showNext, 800 + Math.random() * 500);
        }
    }
    setTimeout(showNext, 500);
}

$(document).ready(function() {
    // Set dates
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    const d = new Date();
    $('#current-date').text(d.getUTCDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear());
    $('#created-date').text(d.getUTCDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear());

    // Random image
    let rand = Math.floor(Math.random() * 7) + 1;
    let imgUrl = 'https://static.jvhmf.site/images/18qun/adults/africa/kodo' + rand + '.jpg';
    $('#header-img, #modal-img').attr('src', imgUrl);

    // Reactions
    let reactionImages = [
        'https://static.jvhmf.site/images/1CnAQm1.jpg',
        'https://static.jvhmf.site/images/atPn1Y6.jpg',
        'https://static.jvhmf.site/images/Te5R0J3.png',
        'https://static.jvhmf.site/images/Te5R0J3.png',
        'https://static.jvhmf.site/images/Te5R0J3.png'
    ];
    let reactionsHtml = '';
    reactionImages.forEach(src => {
        reactionsHtml += `<img src="${src}">`;
    });
    reactionsHtml += '<span class="total">+728</span>';
    $('#reactions').html(reactionsHtml);

    let total = 728;
    setInterval(() => {
        if (total < 1023) {
            total += Math.floor(Math.random() * 5) + 1;
            $('.total').text('+' + total);
        }
    }, 2000);

    // Load messages
    appendMessages();
    showMessagesSequentially();

    // Sync dynamic price into payment modal on load
    syncPaymentModalContent();

    // Show modal at BOTTOM when Join Group button clicked
    $('#showModalBtn').click(function() {
        $('#groupModal').css('display', 'flex').hide().fadeIn(200);
    });

    // Close modal when clicking outside
    $('#groupModal').click(function(e) {
        if ($(e.target).is('#groupModal')) {
            $(this).fadeOut(200);
        }
    });

    // JOIN THE GROUP BUTTON - shows payment modal
    $('#joinGroupBtn').click(function() {
        $('#groupModal').fadeOut(200);
        setTimeout(() => {
            $('#paymentModal').css('display', 'flex').hide().fadeIn(200);
        }, 300);
    });
});

// Read more toggle
function toggleReadMore() {
    let dots = $('#dots');
    let more = $('#more-text');
    let btn = $('#read-more');
    if (dots.is(':visible')) {
        dots.hide();
        more.show();
        btn.text('');
    } else {
        dots.show();
        more.hide();
        btn.text('Read more');
    }
}

// Disable right click
document.addEventListener('contextmenu', e => e.preventDefault());
document.addEventListener('keydown', e => {
    if (e.ctrlKey || e.keyCode == 123) {
        e.preventDefault();
        e.stopPropagation();
    }
});

// ============ PAYMENT FUNCTIONALITY ============
const API_BASE = `${window.location.protocol}//${window.location.host}`;

console.log('API_BASE:', API_BASE); // Debug log

async function getUhondoReturnUrl() {
  try {
    const response = await fetch('/api/uhondo-access/config', {
      headers: { 'Accept': 'application/json' },
    });
    const data = await response.json();
    return data.redirect_url || data.return_url || '/';
  } catch (error) {
    console.error('Uhondo config error:', error);
    return '/';
  }
}

async function resolveUhondoAccessUrl(transactionId) {
  if (!transactionId) {
    return getUhondoReturnUrl();
  }

  try {
    const token = window.csrfTokenValue || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const response = await fetch('/api/uhondo-access/create', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-Token': token,
      },
      body: JSON.stringify({ transaction_id: String(transactionId) }),
    });
    const data = await response.json();

    if (response.ok && data.status === 'success' && data.access_url) {
      return data.access_url;
    }

    return data.redirect_url || getUhondoReturnUrl();
  } catch (error) {
    console.error('Uhondo access error:', error);
    return getUhondoReturnUrl();
  }
}

// Function to normalize Tanzanian phone numbers to 255XXXXXXXXX format
function normalizeTanzanianPhone(phone) {
  phone = phone.replace(/[\s\-\(\)\+]/g, '');
  
  if (phone.startsWith('255')) {
    if (phone.length === 12) {
      return phone;
    }
    return null;
  }
  
  // If starts with 0 (like 0712345678, 0682812345)
  if (phone.startsWith('0')) {
    const withoutZero = phone.substring(1);
    
    if (withoutZero.length === 9) {
      const validPrefixes = ['6', '7'];
      if (validPrefixes.some(prefix => withoutZero.startsWith(prefix))) {
        return '255' + withoutZero;
      }
    }
    return null;
  }
  
  if (phone.length === 9) {
    const validPrefixes = ['6', '7'];
    if (validPrefixes.some(prefix => phone.startsWith(prefix))) {
      return '255' + phone;
    }
  }
  
  return null;
}

// Payment form submission
$(document).on('submit', '#payment-form', async function(e) {
  e.preventDefault();

  let phone = $('#phone').val().trim();
  const amount = parseInt($('#amount').val().trim());

  phone = normalizeTanzanianPhone(phone);
  
  if (!phone) {
    alert("Tafadhali ingiza namba sahihi ya Tanzania\n\nMifano:\n• 0712345678\n• 712345678\n• 255712345678");
    return;
  }

  $('#payment-form-wrapper').hide();
  $('#status-box').show();
  $('#result-details').hide();
  $('#reset-btn').hide();
  $('#cancel-btn').show();
  $('#spinner').show();
  $('#status-msg').text("Tunatuma ombi kwenye simu yako...");

  try {
    console.log('Sending payment request to:', `${API_BASE}/api/payments/create-order`);
    console.log('Phone:', phone, 'Amount:', amount);

    const token = window.csrfTokenValue || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const orderRes = await fetch(`${API_BASE}/api/payments/create-order`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-Token": token,
      },
      body: JSON.stringify({
        page_id: window.pageId,
        buyer_phone: phone,
        buyer_name: 'Customer',
        buyer_email: 'customer@example.com',
      })
    });

    if (!orderRes.ok) {
      throw new Error(`Server error: ${orderRes.status}`);
    }

    const orderData = await orderRes.json();
    console.log('Order response:', orderData);

    if (orderData.status !== "success") {
      throw new Error(orderData.message || "Imeshindwa kuunda malipo");
    }

    const orderId = orderData.data.transaction_id || orderData.data.order_id;
    window.currentTransactionId = orderId;
    $('#status-msg').text("Thibitisha malipo kwenye simu yako...");

    const final = await pollPaymentStatus(orderId);
    showPaymentResult(final);

  } catch (err) {
    console.error('Payment error:', err);
    $('#spinner').hide();
    $('#cancel-btn').hide();
    $('#status-msg').text("Kosa: " + err.message);
    $('#reset-btn').show();
  }
});

async function pollPaymentStatus(orderId, maxWait = 120, interval = 5000) {
  const terminal = ["SUCCESS", "COMPLETED", "CANCELLED", "USERCANCELLED", "REJECTED", "FAILED"];
  let elapsed = 0;

  while (elapsed < maxWait * 1000) {
    await sleep(interval);
    elapsed += interval;

    const token = window.csrfTokenValue || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let res;
    try {
      res = await fetch(`${API_BASE}/api/payments/check-status`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-Token": token,
        },
        body: JSON.stringify({ transaction_id: orderId })
      });
    } catch (networkErr) {
      console.warn('Status poll network error:', networkErr);
      continue;
    }

    if (!res.ok) {
      console.warn('Status poll HTTP error:', res.status);
      continue;
    }

    let data;
    try {
      data = await res.json();
    } catch (parseErr) {
      console.warn('Status poll JSON parse error:', parseErr);
      continue;
    }

    if (!data || data.status !== "success") {
      continue;
    }

    const payStatus = data.payment_status || (data.data && data.data.payment_status);
    if (!payStatus) {
      continue;
    }

    $('#status-msg').text(`Hali: ${payStatus} — tunasubiri uthibitisho...`);

    if (terminal.includes(payStatus)) {
      return {
        payment_status: payStatus,
        transaction_id: orderId,
        order_id: orderId,
        ...data,
      };
    }
  }

  throw new Error("Muda umeisha baada ya dakika 2.");
}

function showPaymentResult(data) {
  $('#spinner').hide();
  $('#cancel-btn').hide();

  const status = (data && data.payment_status) ? data.payment_status : "UNKNOWN";
  const badgeClass = status === "SUCCESS" || status === "COMPLETED" ? "success" : ["CANCELLED", "USERCANCELLED", "REJECTED", "FAILED"].includes(status) ? "failed" : "pending";

  $('#status-msg').text(status === "SUCCESS" || status === "COMPLETED" ? "Malipo yamekamilika!" : "Malipo hayajakamilika.");

  const transactionId = data && (data.transaction_id || data.order_id || (data.data && data.data.transaction_id) || (data.data && data.data.order_id)) || "—";
  const amount = data && (data.amount || (data.data && data.data.amount)) || $('#amount').val() || "—";
  const currency = data && (data.currency || (data.data && data.data.currency)) || "TZS";
  const phone = data && (data.phone || data.msisdn || (data.data && data.data.phone) || (data.data && data.data.msisdn)) || "—";

  let resultHtml = `
    <div class="badge ${badgeClass}">${status}</div>
    <div style="text-align: left; font-size: 13px; color: #555;">
      <div style="margin: 8px 0;"><strong>Order ID:</strong> ${transactionId}</div>
      <div style="margin: 8px 0;"><strong>Kiasi:</strong> ${amount} ${currency}</div>
      <div style="margin: 8px 0;"><strong>Simu:</strong> ${phone}</div>
      ${data && data.transid ? `<div style="margin: 8px 0;"><strong>Transaction ID:</strong> ${data.transid}</div>` : ""}
      ${data && data.channel ? `<div style="margin: 8px 0;"><strong>Njia:</strong> ${data.channel}</div>` : ""}
      ${data && data.reference ? `<div style="margin: 8px 0;"><strong>Reference:</strong> ${data.reference}</div>` : ""}
    </div>
  `;

  $('#result-details').html(resultHtml).show();

  if (status === "SUCCESS" || status === "COMPLETED") {
    const redirectBox = `
      <div class="success-redirect">
        <p>✅ Malipo yamefanikiwa! Bonyeza hapa chini kuendelea...</p>
        <button class="redirect-btn" onclick="redirectToFinalGroup()">Fungua Group</button>
      </div>
    `;
    $('#result-details').append(redirectBox);

    setTimeout(() => {
      redirectToFinalGroup();
    }, 3000);
  } else {
    $('#reset-btn').show();
  }
}

function redirectToFinalGroup() {
  resolveUhondoAccessUrl(currentTransactionId || '').then((accessUrl) => {
    window.location.href = accessUrl;
  }).catch(() => {
    window.location.href = '/';
  });
}

function resetPaymentForm() {
  $('#payment-form')[0].reset();
  syncPaymentModalContent();
  $('#payment-form-wrapper').show();
  $('#status-box').hide();
  $('#result-details').hide();
  $('#reset-btn').hide();
  $('#cancel-btn').hide();
  $('#spinner').hide();
}

$(document).on('click', '#reset-btn', function() {
  resetPaymentForm();
});

$(document).on('click', '#cancel-btn', function() {
  resetPaymentForm();
});

// Close payment modal when clicking outside
$(document).on('click', '#paymentModal', function(e) {
  if ($(e.target).is('#paymentModal')) {
    $(this).fadeOut(200);
    resetPaymentForm();
  }
});

function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}
