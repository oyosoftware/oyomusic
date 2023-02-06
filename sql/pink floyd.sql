select name, released, title, folder
from artists inner join albums on artists.id=artistid
where name='Pink Floyd'