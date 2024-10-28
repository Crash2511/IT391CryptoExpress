from bs4 import BeautifulSoup
import requests

url = ''
page = requests.get(url)
soup = BeautifulSoup(page.text, 'html')

print(soup)

soup.find('table')

soup.find_all('table')[1]

soup.find('table', class_ = 'our cryptos')

table = soup.find_all('table)[1]
print(table)

world_titles = table.find_all('th')

world_table_titles = [title.text.strip() for title in world_titles]

import pandas as pd

df = pd.DataFrame(colimns = world_table_titles)
column_data = table.find_all('tr')
for row in column_data[1:]:
  row_data = row.find_all('td')
  individual_row_data = [data.text.strip() for data in row_data]
  length = len(df)
  df.loc[length] = individual_row_data

df.to_csv('pathway idk where', index = False)




///////////////////////////

from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.common.by import By
import datetime as dt
import pandas as pd

# Opening the connection and grabbing the page
my_url = 'https://www.google.com/webhp?hl=en'
option = Options()
option.headless = False
driver = webdriver.Chrome(options=option)
driver.get(my_url)
driver.maximize_window()


action = webdriver.ActionChains(driver)
search_bar = WebDriverWait(driver,
                           20).until(EC.presence_of_element_located((By.XPATH, '/html/body/div[1]/div[3]/form/div[2]/div[1]'
                                                                           '/div[1]/div/div[2]/input')))
search_button = WebDriverWait(driver,
                              20).until(EC.element_to_be_clickable((By.XPATH, '/html/body/div[1]/div[3]/form/div[2]/'
                                                                              'div[1]/div[3]/center/input[1]')))

search_bar.send_keys('dollar euro')
search_button.click()


element = WebDriverWait(driver,
                              20).until(EC.presence_of_element_located((By.XPATH, '/html/body/div[6]/div[2]/div[9]/div[1]/'
                                                                              'div[2]/div/div[2]/div[2]/div/div/div[1]/'
                                                                              'div/div/div/div/div/div[2]/div/div[2]/div'
                                                                              '/div')))
loc = element.location
size = element.size

print(loc)
print(size)


date = driver.find_element_by_xpath('/html/body/div[6]/div[2]/div[9]/div[1]/div[2]/div/div[2]/div[2]'
                                    '/div/div/div[1]/div/div/div/div/div/div[2]/div/div[2]/div/div/div[1]/'
                                    'span[4]').text
value = driver.find_element_by_xpath('/html/body/div[6]/div[2]/div[9]/div[1]/div[2]/div/div[2]/div[2]/div/div/div[1]/'
                                     'div/div/div/div/div/div[2]/div/div[2]/div/div/div[1]/span[1]').text


limit = dt.datetime.strptime('05/15', '%m/%d')
pace = -5

while True:
    action.move_by_offset(pace, 0).perform()
    date = driver.find_element_by_xpath(
        '/html/body/div[6]/div[2]/div[9]/div[1]/div[2]/div/div[2]/div[2]/div/div/div[1]/div/div/div/div/div/'
        'div[2]/div/div[2]/div/div/div[1]/span[4]').text
    value = driver.find_element_by_xpath(
        '/html/body/div[6]/div[2]/div[9]/div[1]/div[2]/div/div[2]/div[2]/div/div/div[1]/div/div/div/div/div/'
        'div[2]/div/div[2]/div/div/div[1]/span[1]').text

    if dt.datetime.strptime(date, '%a, %d %b') < limit:
        break

driver.quit()

dictionary = {}
dictionary[date] = value

limit = dt.datetime.strptime('05/15', '%m/%d')
pace = -5
/////////
while True:
    action.move_by_offset(pace, 0).perform()
    date = driver.find_element_by_xpath(
        '/html/body/div[6]/div[2]/div[9]/div[1]/div[2]/div/div[2]/div[2]/div/div/div[1]/div/div/div/div/div/'
        'div[2]/div/div[2]/div/div/div[1]/span[4]').text
    value = driver.find_element_by_xpath(
        '/html/body/div[6]/div[2]/div[9]/div[1]/div[2]/div/div[2]/div[2]/div/div/div[1]/div/div/div/div/div/'
        'div[2]/div/div[2]/div/div/div[1]/span[1]').text
    
    if dt.datetime.strptime(date, '%a, %d %b') < limit:
        break
        
    if date in dictionary:
        pass
    else:
        dictionary[date] = value
        
driver.quit()

df = pd.DataFrame.from_dict(dictionary, orient='index')






////////////////////







import requests

url = "https://index.minfin.com.ua/ua/economy/index/svg.php?indType=1&fromYear=2010&acc=1"
resp = requests.get(url)
data = resp.text
Then you will create a BeatifulSoup object with this HTML.

from bs4 import BeautifulSoup

soup = BeautifulSoup(html, features="html.parser")
After this, it is usually very subjective how to parse out what you want. The candidate codes may vary a lot. This is how I did it:

Using BeautifulSoup, I parsed all "rect"s and check if "onmouseover" exists in that rect.

rects = soup.svg.find_all("rect")
yx_points = []
for rect in rects:
    if rect.has_attr("onmouseover"):
        text = rect["onmouseover"]
        x_start_index = text.index("'") + 1
        y_finish_index = text[x_start_index:].index("'") + x_start_index
        yx = text[x_start_index:y_finish_index].split()
        print(text[x_start_index:y_finish_index])
        yx_points.append(yx)



from bs4 import BeautifulSoup

import requests

import re

#First get all the text from the url.
url="https://index.minfin.com.ua/ua/economy/index/svg.php?indType=1&fromYear=2010&acc=1"

response = requests.get(url)

html = response.text

#Find all the tags in which the data is stored.

soup = BeautifulSoup(html, 'lxml')

texts = soup.findAll("rect")

final  = []

for each in texts: 

    names = each.get('onmouseover')
    try:
        q = re.findall(r"'(.*?)'", names)
        final.append(q[0])
    except Exception as e:
        print(e)

#The details are appended to the final variable




