// Input format: array of objects (JSON)
// Example [{...props},{...props}]
const transform = (source) => {
    return source.reduce((acc, element) => {
        const namePattern = `${element['sku']}_${element['warehouse']}`;
        const currentObj = {[namePattern]:element};
      return {...currentObj, ...acc};
    }, {});
};

const test = [{"a":1,"b":2,"c":3},{"a":4,"b":5,"c":6},{"a":7,"b":8,"c":9}];
const test2 = [{"pr_0":"69,730.33","sku":"550014293","warehouse":"msk","manufacturer":"SHELL","name":"ATF 134 209L","quantity":"100.00","pr_3":"66,409.84","pr_2":"68,402.13","pr_1":"67,073.93"},{"pr_0":"83,792.61","sku":"550023517","warehouse":"msk","manufacturer":"SHELL","name":"ATF 134 FE 209L","quantity":"100.00","pr_3":"79,802.49","pr_2":"82,196.56","pr_1":"80,600.51"}];
console.log(JSON.stringify(transform(test2)));