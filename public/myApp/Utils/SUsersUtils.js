class SUsersUtils {
    getUserData(route, user_id){
        return new Promise((resolve) => 
            axios.post(route, {
                'user_id':  user_id
            })
            .then(response => {
                let data = response.data;
                if(data.success){
                    resolve(data.oUser);
                }else{
                    resolve(null);
                }
            })
            .catch( function (error){
                console.log(error);
            })
        )
    }
}