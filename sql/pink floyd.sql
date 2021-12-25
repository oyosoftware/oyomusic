SELECT name, released, title, folder
from artists inner join albums on artists.id=artistid
where artistid=88 and folder like "/Populair%"