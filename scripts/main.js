
        var before = document.getElementById('before');
        var command = document.getElementById("input");
        var textarea = document.getElementById("textbox");
        var terminal = document.getElementById("terminal");
        var git = 0;
        var commands = [];

        // Function to print the contents of a list line by line
        function printLines(name, style, time) {
            name.forEach((item, index) => {
                addLine(item, style, index * time);
            });
        }

        // Function to add a line to the terminal
        function addLine(text, style, time) {
            // Replace all spaces with nbsp character
            let formattedText = "";
            for (let i = 0; i < text.length; i++) {
                if (text.charAt(i) == " " && text.charAt(i + 1) == " ") {
                    formattedText += "&nbsp;&nbsp;";
                    i++;
                } else {
                    formattedText += text.charAt(i);
                }
            }

            // Create a new 'p' and append it to the terminal just before the before
            setTimeout(() => {
                const next = document.createElement("p");
                next.innerHTML = formattedText;
                next.className = style;

                before.parentNode.insertBefore(next, before);

                window.scrollTo(0, document.body.offsetHeight);
            }, time);
        }

        // Router function
        async function router(cmd) {
            switch (cmd.toLowerCase()) {
                case "help":
                    printLines(help, "color2 margin", 80);
                    break;

                case "about":
                    printLines(about, "color2 margin", 80);
                    break;

                case "banner":
                    printLines(banner, "", 80);
                    break;
                
                case "projects":
                    printLines(projects, "", 80);
                    break;

                case "history":
                    addLine("<br>", "", 0);
                    printLines(commands, "color2", 80);
                    addLine("<br>", "command", 80 * commands.length + 50);
                    break;

                case "email":
                    addLine('Opening mailto:<a href="mailto:shyfx666@gmail.com">shyfx666@gmail.com</a>...', "color2", 80);
                    openNewTab(email);
                    break;

                case "clear":
                    setTimeout(() => {
                        terminal.innerHTML = '<a id="before"></a>';
                        before = document.getElementById("before");
                    }, 1);
                    printLines(banner, "", 80);
                    break;

                case "ls":
                    addLine("So you are a real programmer at heart ❤️. PS. It's just a website!<br>", "color2", 0);
                    break;

                case "cd":
                    addLine("So you are a real programmer at heart ❤️. PS. It's just a website!<br>", "color2", 0);
                    break;

                case "sudo":
                    addLine("Woah Woah Woah!! Relax!!<br>", "color2", 0);
                    break;

                case "exit":
                    window.close();
                    addLine("If the window doesn't close, it might be because of a safety feature! Close the tab manually!", "color2", 0);
                    break;

                case "weather":
                    await fetchWeatherByLocation(); // Call the function to get weather
                    break;

                default:
                    addLine("<span class=\"inherit\">Command not found. For a list of commands, type <span class=\"command\">'help'</span>.</span>", "error", 100);
                    break;
            }
        }

        // Function to fetch weather based on browser location
        async function fetchWeatherByLocation() {
            let latitude, longitude;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(async (position) => {
                    latitude = position.coords.latitude;
                    longitude = position.coords.longitude;

                    await getWeather(latitude, longitude);
                }, async (error) => {
                    console.warn('Geolocation denied. Error:', error);

                    // Fallback to city input if location access is denied
                    const cityName = command.innerHTML;

                    if (!cityName) {
                        addLine('Please enter a city name!', "error", 0);
                        return;
                    }

                    // Fetch weather using city input
                    await fetchWeatherByCity();
                });
            } else {
                addLine('Geolocation is not supported by this browser.', "error", 0);
            }
        }

        // Function to fetch weather based on city input
        async function fetchWeatherByCity() {
            const cityName = command.innerHTML;

            if (!cityName) {
                addLine('Please enter a city name!', "error", 0);
                return;
            }

            try {
                // First API call: Get city data
                const cityResponse = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${cityName}&count=1&language=en&format=json`);
                const cityData = await cityResponse.json();

                if (cityData && cityData.results && cityData.results.length > 0) {
                    const city = cityData.results[0];
                    const latitude = city.latitude;
                    const longitude = city.longitude;

                    await getWeather(latitude, longitude);
                } else {
                    addLine('City not found!', "error", 0);
                }
            } catch (error) {
                console.error('Error fetching city data:', error);
                addLine('Failed to fetch city data.', "error", 0);
            }
        }

        // Function to get weather based on latitude and longitude
        async function getWeather(latitude, longitude) {
            try {
                const weatherResponse = await fetch(`https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current_weather=true&forecast_days=1&timezone=Europe%2FBerlin`);
                const weatherData = await weatherResponse.json();

                if (weatherData.current_weather) {
                    const currentTemp = weatherData.current_weather.temperature;
                    addLine(`Location: (${latitude}, ${longitude})\nCurrent Temperature: ${currentTemp}°C`, "color2", 0);
                } else {
                    addLine('Weather data not available!', "error", 0);
                }
            } catch (error) {
                console.error('Error fetching weather data:', error);
                addLine('Failed to fetch weather data.', "error", 0);
            }
        }

        // Initial banner printing
        setTimeout(() => {
            printLines(banner, "", 80);
            textarea.focus();
        }, 100);

        // Open listeners and initially set the text and history to blank
        window.addEventListener("keyup", enterKey);
        textarea.value = "";
        command.innerHTML = textarea.value;

        // Handles the pressing of different key presses
        function enterKey(e) {
            // Reload
            if (e.keyCode == 181) {
                document.location.reload(true);
            }
            // Enter Key
            if (e.keyCode == 13) {
                commands.push(command.innerHTML);
                git = commands.length;
                addLine("visitor:~$ " + command.innerHTML, "no-animation", 0);
                router(command.innerHTML.toLowerCase());
                command.innerHTML = "";
                textarea.value = "";
            }
            // Previous commands (up)
            if (e.keyCode == 38 && git != 0) {
                git -= 1;
                textarea.value = commands[git];
                command.innerHTML = textarea.value;
            }
            // Next Command (down)
            if (e.keyCode == 40 && git != commands.length) {
                git += 1;
                if (commands[git] === undefined) {
                    textarea.value = "";
                } else {
                    textarea.value = commands[git];
                }
                command.innerHTML = textarea.value;
            }
        }
