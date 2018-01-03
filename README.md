# graphql_file_upload


A GraphQL File Upload Module


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

currently works with `multipart/form-date` set on the request body sent as a POST. 

Works out of the box with `apollo-upload-client`


The variable should look like this: 

```$xslt
{
  "file": {"filename": String}
}
```