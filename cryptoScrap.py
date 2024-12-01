def get_crypto_data(symbol, retries=3):
    url = f'https://finance.yahoo.com/quote/{symbol}'
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36"
    }
    attempt = 0
    while attempt < retries:
        try:
            logging.info(f"Fetching data for {symbol}, attempt {attempt + 1}")
            r = requests.get(url, headers=headers, timeout=5)
            if r.status_code == 200:
                soup = BeautifulSoup(r.text, 'html.parser')

                # Fetch price
                price_element = soup.find('fin-streamer', {'data-field': 'regularMarketPrice'})
                price = Decimal(price_element.text.replace(',', '').strip()) if price_element else Decimal('0.0000000000')

                # Fetch price change
                price_change_element = soup.find('fin-streamer', {'data-field': 'regularMarketChange'})
                price_change_text = price_change_element.text if price_change_element else "0.00"
                price_change_text = re.sub(r'[^\d.-]', '', price_change_text)
                price_change = Decimal(price_change_text) if price_change_text else Decimal('0.0000000000')

                # Fetch percent change
                change_percent_element = soup.find('fin-streamer', {'data-field': 'regularMarketChangePercent'})
                change_percent_text = change_percent_element.text if change_percent_element else "0.00%"
                change_percent_text = re.sub(r'[^\d.-]', '', change_percent_text)
                change_percent = Decimal(change_percent_text) if change_percent_text else Decimal('0.00')

                # Initialize market_cap, volume, circulating_supply
                market_cap = "N/A"
                volume = Decimal('0.00')
                circulating_supply = Decimal('0.00')

                # Fetch alternative fields for market cap and others
                summary_table = soup.find_all('div', {'data-test': 'summary-table'})
                if summary_table:
                    for div in summary_table:
                        try:
                            rows = div.find_all('tr')
                            for row in rows:
                                header = row.find('td', {'class': 'C($primaryColor)'})
                                value = row.find('td', {'class': 'Ta(end)'})
                                if header and value:
                                    header_text = header.text.strip()
                                    value_text = value.text.strip()
                                    if 'Market Cap' in header_text:
                                        market_cap = value_text
                                    elif 'Volume' in header_text and '24hr' not in header_text:
                                        volume = convert_to_decimal(value_text)
                                    elif 'Circulating Supply' in header_text:
                                        circulating_supply = convert_to_decimal(value_text)
                        except Exception as e:
                            logging.warning(f"Error parsing summary table for {symbol}: {e}")

                return {
                    'name': symbol,
                    'name_abreviation': symbol.split('-')[0],
                    'price': price,
                    'price_change': price_change,
                    'change_percent': change_percent,
                    'market_cap': market_cap,
                    'volume': volume,
                    'circulating_supply': circulating_supply,
                    'trade_time': datetime.now(timezone.utc)
                }
            else:
                logging.error(f"Failed to fetch data for {symbol}: HTTP {r.status_code}")
                attempt += 1
                time.sleep(2)
        except Exception as e:
            logging.error(f"Error getting data for {symbol}, attempt {attempt + 1}: {e}")
            attempt += 1
            time.sleep(2)
    return None

# Function to convert text with suffixes like T/B/M into Decimal
def convert_to_decimal(value_text):
    value_text = value_text.replace(',', '').strip()
    if 'T' in value_text:
        return Decimal(value_text.replace('T', '')) * Decimal(1e12)
    elif 'B' in value_text:
        return Decimal(value_text.replace('B', '')) * Decimal(1e9)
    elif 'M' in value_text:
        return Decimal(value_text.replace('M', '')) * Decimal(1e6)
    try:
        return Decimal(value_text)
    except:
        return Decimal('0.00')



