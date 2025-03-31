<button>Dodaj ofertę</button>
<button>Publikuj ogłoszenie</button>

<label>
  Tytuł oferty
  <input id="jobOfferTitle">
</label>
<button id="add">Dodaj</button>

<input placeholder="Wyszukaj">
<button data-testid="search">S</button>
<ul id="jobOffers"></ul>

<script>
  document.querySelector('#add').addEventListener('click', function () {
    const title = document.querySelector('#jobOfferTitle').value;
    const jobOffer = document.createElement('li');
    jobOffer.dataset.testid = 'jobOfferTitle';
    jobOffer.textContent = title;
    document.querySelector('#jobOffers').appendChild(jobOffer);
  });
</script>
