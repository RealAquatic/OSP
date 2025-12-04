const searchInput = document.getElementById("animalSearch");
const searchBtn = document.getElementById("searchBtn");
const animalDatalist = document.getElementById("animalList");
const validationMessage = document.getElementById("validationMessage");

// Your API key
const API_KEY = "Iyo/xl8aYY31fuSJ8MCfIw==U1dNnsRi9XXe3obV";

// List of valid animals
const validAnimals = [
    "lion", "tiger", "elephant", "giraffe", "penguin", "koala",
    "panda", "bear", "wolf", "kangaroo", "zebra", "sloth", "rhino",
    "hippo", "leopard", "cheetah", "alligator", "crocodile", "eagle",
    "owl", "meerkat", "lemur"
];

// Populate datalist for autocomplete
validAnimals.forEach(animal => {
    const option = document.createElement("option");
    option.value = animal;
    animalDatalist.appendChild(option);
});

// Search function
function searchAnimal(name) {
    validationMessage.textContent = "";

    if (!name || name.trim() === "") {
        validationMessage.textContent = "Please enter an animal name.";
        return;
    }

    const animal = name.trim().toLowerCase();

    if (!validAnimals.includes(animal)) {
        validationMessage.textContent = `"${name}" is not available. Please choose from the list.`;
        return;
    }

    const encoded = encodeURIComponent(animal);
    window.location.href = `AnimalInformation.html?animal=${encoded}`;
}

// Event listeners
searchBtn.addEventListener("click", () => searchAnimal(searchInput.value));
searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") searchAnimal(searchInput.value);
});

/* ----------------------------
   Load Animal Info on page
----------------------------*/

const urlParams = new URLSearchParams(window.location.search);
const animalName = urlParams.get("animal");
const searchHeader = document.getElementById("Search");
const contentDiv = document.querySelector(".Content");

if (searchHeader) {
    searchHeader.textContent = `Currently searching for: ${animalName ? decodeURIComponent(animalName) : "Nothing"}`;
}

async function fetchAnimalData(name) {
    if (!name) return;

    try {
        const res = await fetch(`https://api.api-ninjas.com/v1/animals?name=${name}`, {
            headers: { "X-Api-Key": API_KEY }
        });

        if (!res.ok) throw new Error("API request failed");

        const data = await res.json();

        if (!data.length) {
            showError(`No animal found for "${name}".`);
            return;
        }

        displayAnimalInfo(data[0]);
    } catch (err) {
        console.error(err);
        showError("Failed to load animal information.");
    }
}

function displayAnimalInfo(animal) {
    const infoBox = document.createElement("div");
    infoBox.className = "AnimalInfoBox";

    const taxonomy = animal.taxonomy || {};
    const characteristics = animal.characteristics || {};

    infoBox.innerHTML = `
        <h2>${animal.name}</h2>
        <p><strong>Scientific Class:</strong> ${taxonomy.class || "Unknown"}</p>
        <p><strong>Family:</strong> ${taxonomy.family || "Unknown"}</p>
        <p><strong>Genus:</strong> ${taxonomy.genus || "Unknown"}</p>
        <p><strong>Diet:</strong> ${characteristics.diet || "Unknown"}</p>
        <p><strong>Habitat:</strong> ${characteristics.habitat || "Unknown"}</p>
        <p><strong>Lifespan:</strong> ${characteristics.lifespan || "Unknown"}</p>
        <p><strong>Location:</strong> ${animal.locations?.length ? animal.locations.join(", ") : "Unknown"}</p>
        <p><strong>Weight:</strong> ${characteristics.weight || "Unknown"}</p>
        <p><strong>Type:</strong> ${characteristics.type || "Unknown"}</p>
    `;

    contentDiv.appendChild(infoBox);
}

function showError(msg) {
    const err = document.createElement("p");
    err.style.color = "darkred";
    err.style.fontSize = "24px";
    err.style.marginLeft = "4vw";
    err.textContent = msg;
    contentDiv.appendChild(err);
}

if (animalName) {
    fetchAnimalData(animalName);
    // Autofill the search input
    searchInput.value = decodeURIComponent(animalName);
}
