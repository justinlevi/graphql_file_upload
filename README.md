# graphql_custom_file_upload


A Custom GraphQL File Upload Module

WIP - CURRENTLY BROKEN :(


gql 
```
mutation($file: FileInput!) {
  uploadFile(input: $file){
    entity{
      ...on FileFile {
        url
      }
    }
  }
}
```


The variable should look like this: 

```$xslt
{
  "file": {"file": __BINARY-FILE-DATA__}
}
```