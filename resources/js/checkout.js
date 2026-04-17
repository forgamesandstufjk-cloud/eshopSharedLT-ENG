import { loadStripe } from "@stripe/stripe-js";

document.addEventListener("DOMContentLoaded", async () => {
  const form = document.getElementById("checkout-form");
  const errorBox = document.getElementById("checkout-error");
  const payButton = document.getElementById("pay-button");
  const carrierSelect = document.getElementById("shipping-carrier");

  if (!form) return;

  const stripeKey = document
    .querySelector('meta[name="stripe-key"]')
    ?.getAttribute("content");

  const csrf = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute("content");

  if (!stripeKey || !csrf) {
    errorBox.textContent = "Stripe konfigūracijos klaida.";
    errorBox.classList.remove("hidden");
    return;
  }

  const stripe = await loadStripe(stripeKey);

  const serviceOrderId = document
    .querySelector('meta[name="service-order-id"]')
    ?.getAttribute("content");

  const isServiceMode = !!serviceOrderId;

  let elements = null;
  let checkoutId = null;
  let intentCreated = false;
  let paymentMounted = false;

  const format = (cents) => `€${(cents / 100).toFixed(2)}`;

  function resetPaymentElement() {
    const paymentEl = document.getElementById("payment-element");
    if (paymentEl) paymentEl.innerHTML = "";
    elements = null;
    paymentMounted = false;
  }

  async function createCartIntent() {
    const intentRes = await fetch("/checkout/intent", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": csrf,
      },
      body: JSON.stringify({
        address: document.querySelector('input[name="address"]').value,
        city: document.querySelector('input[name="city"]').value,
        country: document.querySelector('input[name="country"]').value,
        postal_code: document.getElementById("postal_code").value,
      }),
    });

    const intentData = await intentRes.json();

    if (!intentRes.ok || !intentData.client_secret || !intentData.order_id) {
      throw new Error(
        intentData?.error || "Nepavyko inicijuoti mokėjimo, nes nevisa informacija užpildyta"
      );
    }

    checkoutId = intentData.order_id;
    intentCreated = true;

    document.getElementById("items-total").textContent = format(
      intentData.breakdown.items_total_cents
    );

    const smallOrderRow = document.getElementById("small-order-row");
    const smallOrderFee = document.getElementById("small-order-fee");

    if (smallOrderRow && smallOrderFee) {
      if (intentData.breakdown.small_order_fee_cents > 0) {
        smallOrderFee.textContent = format(intentData.breakdown.small_order_fee_cents);
        smallOrderRow.classList.remove("hidden");
      } else {
        smallOrderRow.classList.add("hidden");
      }
    }

    document.getElementById("shipping-total").textContent = format(
      intentData.breakdown.shipping_total_cents
    );
    document.getElementById("order-total").textContent = format(
      intentData.breakdown.total_cents
    );

    resetPaymentElement();

    elements = stripe.elements({
      clientSecret: intentData.client_secret,
    });

    elements.create("payment").mount("#payment-element");
    paymentMounted = true;
  }

  async function previewCartShipping() {
    if (!checkoutId || !carrierSelect?.value) return;

    const res = await fetch("/checkout/shipping/preview", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": csrf,
      },
      body: JSON.stringify({
        order_id: checkoutId,
        carrier: carrierSelect.value,
      }),
    });

    const data = await res.json();

    if (!res.ok) {
      throw new Error(data?.error || "Nepavyko peržiūrėti pristatymo kainos");
    }

    document.getElementById("shipping-total").textContent = format(data.shipping_total_cents);
    document.getElementById("order-total").textContent = format(data.total_cents);
  }

  async function createServiceIntent() {
    const intentRes = await fetch("/checkout/intent", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
        "X-CSRF-TOKEN": csrf,
      },
      body: JSON.stringify({
        address: document.querySelector('input[name="address"]').value,
        city: document.querySelector('input[name="city"]').value,
        country: document.querySelector('input[name="country"]').value,
        postal_code: document.getElementById("postal_code").value,
        service_order_id: serviceOrderId,
        carrier: carrierSelect?.value || "",
      }),
    });

    const intentData = await intentRes.json();

    if (!intentRes.ok || !intentData.client_secret || !intentData.service_order_id) {
      throw new Error(intentData?.error || "Nepavyko inicijuoti mokėjimo");
    }

    checkoutId = intentData.service_order_id;
    intentCreated = true;

    document.getElementById("items-total").textContent = format(intentData.breakdown.items_total_cents);
document.getElementById("shipping-total").textContent = format(intentData.breakdown.shipping_total_cents ?? 0);
document.getElementById("order-total").textContent = format(intentData.breakdown.total_cents);

if ((intentData.breakdown.small_order_fee_cents ?? 0) > 0) {
  document.getElementById("small-order-fee").textContent =
    format(intentData.breakdown.small_order_fee_cents);
  document.getElementById("small-order-row").classList.remove("hidden");
} else {
  document.getElementById("small-order-row").classList.add("hidden");
}

    resetPaymentElement();

    elements = stripe.elements({
      clientSecret: intentData.client_secret,
    });

    elements.create("payment").mount("#payment-element");
    paymentMounted = true;
  }

  if (carrierSelect) {
    carrierSelect.addEventListener("change", async () => {
      errorBox.classList.add("hidden");

      try {
        if (isServiceMode) {
          if (!carrierSelect.value) {
            resetPaymentElement();
            intentCreated = false;
            checkoutId = null;
            document.getElementById("shipping-total").textContent = "—";
            document.getElementById("order-total").textContent = "—";
            return;
          }

          await createServiceIntent();
          return;
        }

        if (intentCreated) {
          await previewCartShipping();
          return;
        }

        await createCartIntent();
        await previewCartShipping();
      } catch (err) {
        errorBox.textContent = err.message;
        errorBox.classList.remove("hidden");
      }
    });
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    errorBox.classList.add("hidden");

    if (!carrierSelect?.value) {
      errorBox.textContent = "Prašome pasirinkti pristatymo būdą.";
      errorBox.classList.remove("hidden");
      return;
    }

    if (!checkoutId || !elements || !paymentMounted) {
      errorBox.textContent = isServiceMode
        ? "Prašome pirmiausia pasirinkti pristatymo būdą."
        : "Prašome pirmiausia pasirinkti pristatymą.";
      errorBox.classList.remove("hidden");
      return;
    }

    payButton.disabled = true;
    payButton.textContent = "Apdorojama…";

    try {
      if (!isServiceMode) {
        const shipRes = await fetch("/checkout/shipping", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            "X-CSRF-TOKEN": csrf,
          },
          body: JSON.stringify({
            order_id: checkoutId,
            carrier: carrierSelect.value,
          }),
        });

        const shipData = await shipRes.json();

        if (!shipRes.ok) {
          throw new Error(shipData?.error || "Nepavyko patvirtinti pristatymo");
        }

        document.getElementById("shipping-total").textContent = format(shipData.shipping_total_cents);
        document.getElementById("order-total").textContent = format(shipData.total_cents);
      }

      const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
          return_url: isServiceMode
            ? `${window.location.origin}/checkout/success?service_order_id=${encodeURIComponent(checkoutId)}`
            : `${window.location.origin}/checkout/success?order_id=${encodeURIComponent(checkoutId)}`,
        },
      });

      if (error) throw error;
    } catch (err) {
      errorBox.textContent = err.message || "Mokėjimas nepavyko.";
      errorBox.classList.remove("hidden");
      payButton.disabled = false;
      payButton.textContent = "Mokėti dar kartą";
    }
  });
});
